<?php

namespace Vifeed\CampaignBundle\Tests\Controller;

use Vifeed\CampaignBundle\Entity\Tag;
use Vifeed\SystemBundle\Tests\ApiTestCase;

class TagControllerTest extends ApiTestCase
{

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $tag1 = new Tag();
        $tag1->setName('test1');
        self::$em->persist($tag1);
        $tag2 = new Tag();
        $tag2->setName('test2');
        self::$em->persist($tag2);
        self::$em->flush();

        self::$parameters['id1'] = $tag1->getId();
        self::$parameters['id2'] = $tag2->getId();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        self::$em->remove(self::$em->getRepository('VifeedCampaignBundle:Tag')->find(self::$parameters['id1']));
        self::$em->remove(self::$em->getRepository('VifeedCampaignBundle:Tag')->find(self::$parameters['id2']));
        self::$em->flush();
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
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());

        $this->sendRequest('GET', $url);

        $expected = preg_replace_callback(
            '/%%id(\d)%%/',
            function ($match) {
                return self::$parameters['id' . $match[1]];
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
