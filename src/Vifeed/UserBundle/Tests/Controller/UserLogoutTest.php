<?php

namespace Vifeed\UserBundle\Tests\Controller;

use Vifeed\SystemBundle\Tests\ApiTestCase;
use Vifeed\UserBundle\Entity\User;

/**
 * Class UserLogoutTest
 *
 * @package Vifeed\UserBundle\Tests\Controller
 */
class UserLogoutTest extends ApiTestCase
{

    /**
     * логаут без логина
     */
    public function testDeleteTokenUnauthorized()
    {
        $url = self::$router->generate('api_delete_users_token');

        self::$client->request('DELETE', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * logout и удаление api-токена
     */
    public function testDeleteTokenOk()
    {
        /** @var User $user */
        $user = self::$parameters['fixtures']['user'];

        $url = self::$router->generate('api_delete_users_token');
        $this->sendRequest($user, 'DELETE', $url);

        $this->assertEquals(204, self::$client->getResponse()->getStatusCode());

        $tokenManager = self::$client->getContainer()->get('vifeed.user.wsse_token_manager');

        $this->assertNull($tokenManager->getUserToken($user->getId()));
    }

    /**
     * попытка повторного логаута
     *
     * @depends testDeleteTokenOk
     */
    public function testDeleteTokenAgain()
    {
        /** @var User $user */
        $user = self::$parameters['fixtures']['user'];

        $url = self::$router->generate('api_delete_users_token');
        $this->sendRequest($user, 'DELETE', $url);

        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }


    protected static function loadTestFixtures()
    {
        $userManager = self::getContainer()->get('fos_user.user_manager');

        $tokenManager = static::getContainer()->get('vifeed.user.wsse_token_manager');

        /** @var User $advertiser */
        $advertiser = $userManager->createUser();
        $advertiser
              ->setEmail('testadvertiser2@vifeed.ru')
              ->setUsername('testadvertiser2@vifeed.ru')
              ->setBalance(100)
              ->setEnabled(true)
              ->setType(User::TYPE_ADVERTISER)
              ->setPlainPassword('12345');

        $userManager->updateUser($advertiser);


        $tokenManager->createUserToken($advertiser->getId());

        return [
              'user' => $advertiser
        ];
    }


}
