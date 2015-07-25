<?php

namespace Vifeed\UserBundle\Tests\Controller;

use Vifeed\SystemBundle\Tests\ApiTestCase;
use Vifeed\UserBundle\Entity\User;

/**
 * Class UserLoginTest
 *
 * @package Vifeed\UserBundle\Tests\Controller
 */
class UserLoginTest extends ApiTestCase
{

    /**
     * ошибки при логине
     *
     * @param array $data
     * @param array $errors
     *
     * @dataProvider loginErrorsProvider
     */
    public function testLoginErrors($data, $errors)
    {
        $url = self::$router->generate('sign_in');

        self::$client->request('POST', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(401, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertJson($content);

        $content = json_decode($content, JSON_UNESCAPED_UNICODE);
        $this->assertCount(1, $content);

        $this->assertArrayHasKey('message', $content);
        $this->assertEquals($errors, $content['message']);

        $this->assertNull(self::$client->getContainer()->get('security.context')->getToken());
    }

    /**
     * успешный логин
     *
     * @param array $data
     * @param array $fixtures
     *
     * @dataProvider loginOkProvider
     */
    public function testLoginOk($data, $fixtures)
    {
        $url = self::$router->generate('sign_in');

        self::$client->request('POST', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertTrue(self::$client->getContainer()->get('security.context')->isGranted('ROLE_USER'));

        $wsseToken = $this->getCreatedWsseToken();
        $this->assertNotNull($wsseToken);

        $content = $response->getContent();
        $this->assertJson($content);

        $content = json_decode($content, JSON_UNESCAPED_UNICODE);
        $this->assertInternalType('array', $content);
        $this->assertCount(4, $content);

        $this->assertArrayHasKey('token', $content);
        $this->assertEquals($content['token'], $wsseToken);

        $this->assertArrayHasKey('type', $content);
        $this->assertEquals($content['type'], $fixtures['type']);

        $this->assertArrayHasKey('first_name', $content);
        $this->assertEquals($content['first_name'], $fixtures['first_name']);

        $this->assertArrayHasKey('surname', $content);
        $this->assertEquals($content['surname'], $fixtures['surname']);
    }

    /**
     * апи-токен юзера
     *
     * @param null|int $userId
     *
     * @return string
     */
    private function getCreatedWsseToken($userId = null)
    {
        $tokenManager = self::$client->getContainer()->get('vifeed.user.wsse_token_manager');
        if ($userId === null) {
            $userId = self::$client->getContainer()->get('security.context')->getToken()->getUser()->getId();
        }

        return $tokenManager->getUserToken($userId);
    }

    /**
     * data-provider для testLoginErrors
     *
     * @return array
     */
    public function loginErrorsProvider()
    {
        return [
              [[
                     '_username' => 'test',
                     '_password' => 'test1',
               ],
               'Bad credentials'
              ],
              [[
                     '_username' => 'testadvertiser1@vifeed.ru',
                     '_password' => '12345',
               ],
               'User account is disabled.'
              ]
        ];
    }

    /**
     * data-provider для testLoginok
     *
     * @return array
     */
    public function loginOkProvider()
    {
        return [
              [
                    [
                          '_username' => 'testpublisher1@vifeed.ru',
                          '_password' => '12345',
                    ],
                    [
                          'type'       => 'publisher',
                          'first_name' => 'aaa',
                          'surname'    => 'bbb'
                    ]
              ],
              [
                    [
                          '_username' => 'testadvertiser2@vifeed.ru',
                          '_password' => '12345',
                    ],
                    [
                          'type'       => 'advertiser',
                          'first_name' => 'ccc',
                          'surname'    => 'ddd'
                    ]
              ],
        ];
    }


    protected static function loadTestFixtures()
    {
        $userManager = self::getContainer()->get('fos_user.user_manager');

        /** @var User $advertiser1 */
        $advertiser1 = $userManager->createUser();
        $advertiser1
              ->setEmail('testadvertiser1@vifeed.ru')
              ->setUsername('testadvertiser1@vifeed.ru')
              ->setBalance(100)
              ->setType(User::TYPE_ADVERTISER)
              ->setPlainPassword('12345');
        $userManager->updateUser($advertiser1);

        /** @var User $advertiser2 */
        $advertiser2 = $userManager->createUser();
        $advertiser2
              ->setEmail('testadvertiser2@vifeed.ru')
              ->setUsername('testadvertiser2@vifeed.ru')
              ->setFirstName('ccc')
              ->setSurname('ddd')
              ->setBalance(100)
              ->setEnabled(true)
              ->setType(User::TYPE_ADVERTISER)
              ->setPlainPassword('12345');

        $userManager->updateUser($advertiser2);

        /** @var User $publisher */
        $publisher = $userManager->createUser();
        $publisher
              ->setEmail('testpublisher1@vifeed.ru')
              ->setUsername('testpublisher1@vifeed.ru')
              ->setFirstName('aaa')
              ->setSurname('bbb')
              ->setBalance(100)
              ->setType(User::TYPE_PUBLISHER)
              ->setEnabled(true)
              ->setConfirmationToken('abcdef12345')
              ->setPlainPassword('12345');

        $userManager->updateUser($publisher);

        return [
              'users' => [$advertiser1, $advertiser2, $publisher]
        ];
    }


}
