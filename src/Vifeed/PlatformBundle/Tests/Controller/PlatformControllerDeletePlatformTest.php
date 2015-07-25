<?php

namespace Vifeed\PlatformBundle\Tests\Controller;

use Vifeed\PlatformBundle\Entity\Platform;
use Vifeed\UserBundle\Entity\User;

/**
 * Class PlatformControllerDeletePlatformTest
 *
 * @package Vifeed\PlatformBundle\Tests\Controller
 */
class PlatformControllerDeletePlatformTest extends PlatformControllerTestCase
{

    /**
     * попытка удалить площадку без авторизации
     */
    public function testDeletePlatformUnauthorized()
    {
        $url = self::$router->generate('api_delete_platform', array('id' => 0));

        self::$client->request('DELETE', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * попытка удалить несуществуюущую площадку
     */
    public function testDeleteNotExistentPlatform()
    {
        $url = self::$router->generate('api_delete_platform', array('id' => 100500));

        // пробуем удалить несуществующую площадку
        $this->sendRequest(self::$testPublisher, 'DELETE', $url, []);
        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());
    }

    /**
     * попытка удалить чужую площадку
     */
    public function testDeleteNotOwnPlatform()
    {
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platforms'][0];

        $url = self::$router->generate('api_delete_platform', array('id' => $platform->getId()));

        $this->sendRequest(self::$testPublisher, 'DELETE', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * удаляем свою площадку
     *
     * @return int
     */
    public function testDeleteOwnPlatform()
    {
        /** @var User $publisher */
        $publisher = self::$parameters['fixtures']['publishers'][0];
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platforms'][0];

        $url = self::$router->generate('api_delete_platform', array('id' => $platform->getId()));

        $this->sendRequest($publisher, 'DELETE', $url, []);
        $this->assertEquals(204, self::$client->getResponse()->getStatusCode());

        return $platform->getId();
    }

    /**
     * проверяем, что площадка удалена
     *
     * @param int $platfromId
     *
     * @depends testDeleteOwnPlatform
     */
    public function testOwnPlatformDeleted($platfromId)
    {
        /** @var User $publisher */
        $publisher = self::$parameters['fixtures']['publishers'][0];

        $url = self::$router->generate('api_get_platform', array('id' => $platfromId));
        $this->sendRequest($publisher, 'GET', $url);
        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());
    }

    /**
     * проверяем, что площадка осталась в базе с пометкой deletedAt
     *
     * @param int $platfromId
     *
     * @depends testDeleteOwnPlatform
     */
    public function testDeletedPlatformRemainsInDB($platfromId)
    {
        $platform = $this->getEntityManager()->getConnection()->fetchAll('SELECT * FROM platform WHERE id = :id', ['id' => $platfromId]);
        $this->assertInternalType('array', $platform);
        $this->assertCount(1, $platform);
        $this->assertArrayHasKey('deleted_at', $platform[0]);
        $this->assertNotNull($platform[0]['deleted_at']);

        $deletedAt = date_parse($platform[0]['deleted_at']);
        $this->assertInternalType('array', $deletedAt);
        $this->assertArrayHasKey('errors', $deletedAt);
        $this->assertEmpty($deletedAt['errors']);
    }

}
