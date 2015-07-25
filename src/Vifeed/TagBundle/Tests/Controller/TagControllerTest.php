<?php

namespace Vifeed\TagBundle\Tests\Controller;

use Vifeed\TagBundle\Entity\Tag;
use Vifeed\SystemBundle\Tests\ApiTestCase;

class TagControllerTest extends ApiTestCase
{

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

        $this->sendRequest(self::$testAdvertiser, 'GET', $url);

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
            array('test1', '["test1"]'),
            array('te', '["test1","test2"]'),
        );

        return $data;
    }

    /**
     * @return array
     */
    protected static function loadTestFixtures()
    {
        $tag1 = new Tag();
        $tag1->setName('test1');
        self::$em->persist($tag1);

        $tag2 = new Tag();
        $tag2->setName('test2');
        self::$em->persist($tag2);

        self::$em->flush();

        return ['tags' => [
              $tag1->getId() => $tag1,
              $tag2->getId() => $tag2
        ]];
    }

}
