<?php

namespace Vifeed\CampaignBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Vifeed\CampaignBundle\Entity\Tag;

class TagControllerTest extends WebTestCase
{
    /** @var \Symfony\Bundle\FrameworkBundle\Client */
    private static $client;

    /** @var \Symfony\Component\Routing\Router */
    private static $router;

    private static $parameters = array();


    public static function setUpBeforeClass()
    {
        self::$client = static::createClient();
        self::$router = self::$client->getContainer()->get('router');

        $em = self::$client->getContainer()->get('doctrine.orm.entity_manager');
        $tag1 = new Tag();
        $tag1->setName('test1');
        $em->persist($tag1);
        $tag2 = new Tag();
        $tag2->setName('test2');
        $em->persist($tag2);
        $em->flush();

        self::$parameters['id1'] = $tag1->getId();
        self::$parameters['id2'] = $tag2->getId();
    }

    public static function tearDownAfterClass()
    {
        $em = static::createClient()->getContainer()->get('doctrine.orm.entity_manager');
        $em->remove($em->getRepository('CampaignBundle:Tag')->find(self::$parameters['id1']));
        $em->remove($em->getRepository('CampaignBundle:Tag')->find(self::$parameters['id2']));
        $em->flush();
    }

    /**
     * Поиск тегов
     *
     * @dataProvider getTagsProvider
     */
    public function testGetTags($word, $expected)
    {
        $url = self::$router->generate('api_get_tags', array('word' => $word));
        self::$client->request('GET', $url);


        $expected = preg_replace_callback(
            '/%%id(\d)%%/',
            function ($match) {
                return self::$parameters['id'.$match[1]];
            },
            $expected
        );

        $response = self::$client->getResponse();
        $content = $response->getContent();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($content);
        $this->assertEquals($expected, $content);
    }

    /**
     * @return array
     */
    public function getTagsProvider()
    {
        $data = array(
            array('a', '[]'),
            array('test1', '[{"id":%%id1%%,"name":"test1"}]'),
            array('te', '[{"id":%%id1%%,"name":"test1"},{"id":%%id2%%,"name":"test2"}]'),
        );

        return $data;
    }

}
