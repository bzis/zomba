<?php

namespace Vifeed\UserBundle\Tests\Controller;

use Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector;
use Vifeed\SystemBundle\Tests\ApiTestCase;
use Vifeed\UserBundle\Entity\User;

/**
 * Class RegistrationControllerTest
 *
 * @package Vifeed\UserBundle\Tests\Controller
 */
class RegistrationControllerTest extends ApiTestCase
{

    /**
     * Ошибки при регистрации юзера
     *
     * @param array $data
     * @param array $errors
     *
     * @dataProvider userRegisterErrorsProvider
     */
    public function testUserRegisterErrors($data, $errors)
    {
        $url = self::$router->generate('api_put_users');

        self::$client->request('PUT', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());

        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->validateErrors($content, $errors);

        $isAuthenticated = self::$client->getContainer()->get('security.context')->isGranted('ROLE_USER');
        $this->assertFalse($isAuthenticated);
    }

    /**
     * @param array $data
     * @param array $notification
     *
     * @dataProvider userRegisterOkProvider
     *
     * @return string
     */
    public function testUserRegistrationOk($data, $notification)
    {
        $url = self::$router->generate('api_put_users');

        self::$client->request('GET', '/'); // чтобы открыть сессию
        self::$client->enableProfiler();
        self::$client->request('PUT', $url, $data);

        $response = self::$client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());

        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->assertInternalType('array', $content);
        $this->assertCount(1, $content);
        $this->assertArrayHasKey('token', $content);
        $this->assertInternalType('string', $content['token']);

        // юзер залогинен
        $isAuthenticated = self::$client->getContainer()->get('security.context')->isGranted('ROLE_USER');
        $this->assertTrue($isAuthenticated);

        /** @var User $user */
        $user = self::$client->getContainer()->get('security.context')->getToken()->getUser();
        $this->assertTrue($user->isEnabled());
        $this->assertFalse($user->isEmailConfirmed());

        $this->assertEquals($notification, $user->getNotification());

        // письмо отправлено. Хорошо бы вынести в отдельный тест, но трудно
        /** @var MessageDataCollector $mailCollector */
        $mailCollector = self::$client->getProfile()->getCollector('swiftmailer');
        $this->assertEquals(1, $mailCollector->getMessageCount());
        $collectedMessages = $mailCollector->getMessages();

        /** @var \Swift_Message $message */
        $message = $collectedMessages[0];
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertContains('confirm', $message->getBody());

        preg_match('@\/confirm\/([^"]+)"@', $message->getBody(), $matches);
        $token = $matches[1];
        $this->assertGreaterThan(30, strlen($token));
    }

    /**
     * подтверждение е-мейла
     */
    public function testMailConfirmationOk()
    {
        $url = self::$router->generate('api_patch_users_confirm');
        /** @var User $user */
        $user = self::$parameters['fixtures']['users'][1];
        $this->assertFalse($user->isEmailConfirmed());

        $data['token'] = $user->getConfirmationToken();

        self::$client->request('PATCH', $url, $data);

        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        $user = $this->getEntityManager()->find('VifeedUserBundle:User', $user->getId());

        $this->assertTrue($user->isEmailConfirmed());
        $this->assertNull($user->getConfirmationToken());

        $this->assertTrue(self::$client->getContainer()->get('security.context')->isGranted('ROLE_USER'));
    }

    /**
     * неправильный токен
     */
    public function testMailConfirmationWrongToken()
    {
        $url = self::$router->generate('api_patch_users_confirm');

        $data['token'] = '12345';

        self::$client->request('PATCH', $url, $data);

        $this->assertEquals(400, self::$client->getResponse()->getStatusCode());
    }

    /**
     * data-provider для testUserRegisterErrors
     *
     * @return array
     */
    public function userRegisterErrorsProvider()
    {
        $data = [
              [
                    ['registration' => []],
                    ['email' => 'Пожалуйста, укажите Ваш email']
              ],
              [
                    ['registration' => [
                          'email' => 'afasfdsa'
                    ]],
                    ['email' => 'Email в неправильном формате']
              ],
              [
                    ['registration' => [
                          'email' => 'test1@test.test',
                    ]],
                    ['plainPassword' => [
                          'children' => [
                                'first' => 'Пожалуйста, укажите пароль'
                          ]
                    ]]
              ],
              [
                    ['registration' => [
                          'email'         => 'test1@test.test',
                          'plainPassword' => [
                                'first' => 'aaa'
                          ]
                    ]],
                    ['plainPassword' => [
                          'children' => [
                                'first' => 'Пароли должны совпадать!'
                          ]
                    ]]
              ],
              [
                    ['registration' => [
                          'email'         => 'test1@test.test',
                          'plainPassword' => [
                                'first'  => 'aaa',
                                'second' => 'aaa'
                          ]]
                    ],
                    ['plainPassword' => [
                          'children' => [
                                'first' => 'Пароль слишком короткий'
                          ]
                    ]]
              ],
              [
                    ['registration' => [
                          'email' => 'testadvertiser1@vifeed.ru'
                    ]],
                    ['email' => 'Email уже используется']
              ],
              [
                    ['registration' => [
                          'email'         => 'publisheregtest1@test.test',
                          'plainPassword' => [
                                'first'  => 'aaabbb',
                                'second' => 'aaabbb'
                          ]
                    ]],
                    ['type' => 'Значение не должно быть пустым.']
              ],
              [
                    ['registration' => [
                          'email'         => 'publisheregtest1@test.test',
                          'plainPassword' => [
                                'first'  => 'aaabbb',
                                'second' => 'aaabbb'
                          ],
                          'type'          => 'something'
                    ]],
                    ['type' => 'Значение недопустимо.']
              ],

        ];

        return $data;
    }

    /**
     * data-provider для testUserRegistrationOk
     *
     * @return array
     */
    public function userRegisterOkProvider()
    {
        $data = [
              [
                    ['registration' => [
                          'email'         => 'publisheregtest1@test.test',
                          'plainPassword' => [
                                'first'  => 'aaabbb',
                                'second' => 'aaabbb'
                          ],
                          'type'          => 'publisher'
                    ]],
                    ['email' => 1, 'sms' => 0]
              ],
              [
                    ['registration' => [
                          'email'         => 'advertiseregtest1@test.test',
                          'plainPassword' => [
                                'first'  => 'aaabbb',
                                'second' => 'aaabbb'
                          ],
                          'type'          => 'advertiser'
                    ]],
                    ['news' => 1]
              ],
        ];

        return $data;
    }


    protected static function loadTestFixtures()
    {
        $userManager = self::getContainer()->get('fos_user.user_manager');

        /** @var User $advertiser */
        $advertiser = $userManager->createUser();
        $advertiser
              ->setEmail('testadvertiser1@vifeed.ru')
              ->setUsername('testadvertiser1@vifeed.ru')
              ->setBalance(100)
              ->setType(User::TYPE_ADVERTISER)
              ->setPlainPassword('12345');
        $userManager->updateUser($advertiser);

        /** @var User $publisher */
        $publisher = $userManager->createUser();
        $publisher
              ->setEmail('testpublisher1@vifeed.ru')
              ->setUsername('testpublisher1@vifeed.ru')
              ->setBalance(100)
              ->setType(User::TYPE_PUBLISHER)
              ->setEnabled(true)
              ->setConfirmationToken('abcdef12345')
              ->setPlainPassword('12345');

        $userManager->updateUser($publisher);

        return [
              'users' => [$advertiser, $publisher]
        ];
    }


}
