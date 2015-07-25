<?php

namespace Vifeed\PlatformBundle\Tests\Controller;

use Vifeed\PlatformBundle\Entity\Platform;
use Vifeed\PlatformBundle\Entity\VkPlatform;
use Vifeed\UserBundle\Entity\User;

/**
 * Class PlatformControllerEditPlatformTest
 *
 * @package Vifeed\PlatformBundle\Tests\Controller
 */
class PlatformControllerEditPlatformTest extends PlatformControllerTestCase
{
    /**
     * попытка открыть площадку без авторизации
     */
    public function testEditPlatformUnauthorized()
    {
        $url = self::$router->generate('api_put_platform', array('id' => 0));

        self::$client->request('PUT', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * попытка открыть несуществуюущую площадку
     */
    public function testEditNotExistentPlatform()
    {
        $url = self::$router->generate('api_put_platform', array('id' => 100500));

        $this->sendRequest(self::$testPublisher, 'PUT', $url);
        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());
    }

    /**
     * только владелец может изменять данные
     */
    public function testEditNotOwnPlatform()
    {
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platforms'][0];

        $data = ['platform' => ['name' => 'test2']];
        $url = self::$router->generate('api_put_platform', array('id' => $platform->getId()));

        $this->sendRequest(self::$testPublisher, 'PUT', $url, $data);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }


    /**
     * Редактировать площадку
     *
     * @return int
     */
    public function testEditOwnPlatform()
    {
        /** @var User $publisher */
        $publisher = self::$parameters['fixtures']['publishers'][0];

        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platforms'][0];

        $data = ['platform' => ['name' => 'test2']];
        $url = self::$router->generate('api_put_platform', array('id' => $platform->getId()));

        $dateTime = new \DateTime();

        $this->sendRequest($publisher, 'PUT', $url, $data);
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        $data = json_decode(self::$client->getResponse()->getContent(), JSON_UNESCAPED_UNICODE);
        $this->assertEquals('', $data);

        self::$em->refresh($platform);
        $this->assertLessThanOrEqual(2, time() - $platform->getUpdatedAt()->getTimestamp());

        return $platform->getId();
    }

    /**
     * Редактировать площадку VK
     *
     * @return int
     */
    public function testEditOwnVkPlatform()
    {
        /** @var User $publisher */
        $publisher = self::$parameters['fixtures']['publishers'][1];

        /** @var VkPlatform $platform */
        $platform = self::$parameters['fixtures']['platforms'][3];

        $data = ['platform' => ['vkId' => '47777']];
        $url = self::$router->generate('api_put_platform', array('id' => $platform->getId()));

        $this->sendRequest($publisher, 'PUT', $url, $data);
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        $data = json_decode(self::$client->getResponse()->getContent(), JSON_UNESCAPED_UNICODE);
        $this->assertEquals('', $data);

        $platform = $this->getEntityManager()->find('VifeedPlatformBundle:Platform', $platform->getId());
        $this->assertInstanceOf('Vifeed\PlatformBundle\Entity\VkPlatform', $platform);
        $this->assertEquals(47777, $platform->getVkId());
    }

    /**
     * данные о площадке изменены корректно
     *
     * @param int $platformId
     *
     * @depends testEditOwnPlatform
     */
    public function testChangedPlatfromData($platformId)
    {
        /** @var User $publisher */
        $publisher = self::$parameters['fixtures']['publishers'][0];

        $url = self::$router->generate('api_get_platform', array('id' => $platformId));
        $this->sendRequest($publisher, 'GET', $url);
        $data = json_decode(self::$client->getResponse()->getContent(), JSON_UNESCAPED_UNICODE);
        $this->assertEquals('test2', $data['name']);
    }

}
