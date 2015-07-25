<?php

namespace Vifeed\PlatformBundle\Tests\Controller;

use Vifeed\PlatformBundle\Entity\Platform;
use Vifeed\UserBundle\Entity\User;

/**
 * Class PlatformControllerCreatePlatformTest
 *
 * @package Vifeed\PlatformBundle\Tests\Controller
 */
class PlatformControllerGetPlatformsTest extends PlatformControllerTestCase
{

    /**
     * попытка открыть список площадок без авторизации
     */
    public function testGetPlatformListUnauthorized()
    {
        $url = self::$router->generate('api_get_platforms');

        self::$client->request('GET', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * попытка открыть список площадок рекламодателем
     */
    public function testGetPlatformListWithAdvertiser()
    {
        $url = self::$router->generate('api_get_platforms');

        // проверяем, что рекламодатель не может видеть площадки
        $this->sendRequest(self::$testAdvertiser, 'GET', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * площадки, которые видит рекламодатель
     * удалённой площадки $platforms[4] в списке нет
     */
    public function testGetOwnPlatormsList()
    {
        $url = self::$router->generate('api_get_platforms');

        /** @var User $publisher */
        $publisher = self::$parameters['fixtures']['publishers'][1];
        /** @var Platform[] $platforms */
        $platforms = self::$parameters['fixtures']['platforms'];

        $platformIds = [$platforms[1]->getId(), $platforms[2]->getId(), $platforms[3]->getId()];

        $this->sendRequest($publisher, 'GET', $url);
        $response = self::$client->getResponse();
        $content = $response->getContent();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($content);

        $content = json_decode($content, JSON_UNESCAPED_UNICODE);
        $this->assertInternalType('array', $content);
        $this->assertCount(3, $content);

        foreach ($content as $element) {
            $this->assertContains($element['id'], $platformIds);
        }
    }

    /**
     * попытка открыть площадку без авторизации
     */
    public function testGetPlatformUnauthorized()
    {
        $url = self::$router->generate('api_get_platform', array('id' => 0));

        self::$client->request('GET', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * попытка открыть площадку рекламодателем
     */
    public function testGetPlatformWithAdvertiser()
    {
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platforms'][0];

        $url = self::$router->generate('api_get_platform', array('id' => $platform->getId()));

        $this->sendRequest(self::$testAdvertiser, 'GET', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * попытка открыть несуществуюущую площадку
     */
    public function testGetNonExistentPlatform()
    {
        $url = self::$router->generate('api_get_platform', array('id' => -1));

        $this->sendRequest(self::$testPublisher, 'GET', $url);
        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());
    }

    /**
     * попытка открыть удалённую площадку
     */
    public function testGetSoftDeletedPlatform()
    {
        /** @var User $publisher */
        $publisher = self::$parameters['fixtures']['publishers'][1];
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platforms'][4];

        $url = self::$router->generate('api_get_platform', array('id' => $platform->getId()));

        $this->sendRequest($publisher, 'GET', $url);
        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());
    }

    /**
     * попытка открыть паблишером чужую площадку
     */
    public function testGetNotOwnPlatform()
    {
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platforms'][0];

        $url = self::$router->generate('api_get_platform', array('id' => $platform->getId()));

        $this->sendRequest(self::$testPublisher, 'GET', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * открыть свою площадку по id
     */
    public function testGetPlatform()
    {
        /** @var User $publisher */
        $publisher = self::$parameters['fixtures']['publishers'][0];
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platforms'][0];

        $url = self::$router->generate('api_get_platform', array('id' => $platform->getId()));

        $this->sendRequest($publisher, 'GET', $url);
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        $content = self::$client->getResponse()->getContent();
        $this->assertJson($content);

        $data = json_decode($content, JSON_UNESCAPED_UNICODE);
        $this->assertInternalType('array', $data);
        $this->assertArrayHasOnlyKeys(['id', 'hash_id', 'name', 'url', 'description', 'countries', 'tags', 'created_at', 'updated_at'], $data);
        $this->assertEquals($platform->getId(), $data['id']);
    }

}
