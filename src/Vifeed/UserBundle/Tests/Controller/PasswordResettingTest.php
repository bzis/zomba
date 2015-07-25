<?php

namespace Vifeed\UserBundle\Tests\Controller;

use Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector;
use Vifeed\SystemBundle\Tests\ApiTestCase;
use Vifeed\UserBundle\Entity\User;

class PasswordResettingTest extends ApiTestCase
{

    /**
     * ошибки формы запроса сброса пароля
     *
     * @dataProvider sendEmailErrorsProvider
     */
    public function testSendEmailErrors($data, $error)
    {
        $url = self::$router->generate('api_user_reset_password');
        self::$client->request('POST', $url, $data);
        $response = self::$client->getResponse();

        $this->assertEquals(400, $response->getStatusCode(), $response->getContent());

        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals($error, $content['message']);
    }

    /**
     * пользователь отправляет заявку на восстановление пароля
     */
    public function testSendEmailOk()
    {
        $url = self::$router->generate('api_user_reset_password');

        self::$client->request('GET', '/'); // чтобы открыть сессию
        self::$client->enableProfiler();
        self::$client->request('POST', $url, ['email' => 'testadvertiser1@vifeed.ru']);

        $response = self::$client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('""', $response->getContent());

        // юзер не залогинен
        $isAuthenticated = self::$client->getContainer()->get('security.context')->isGranted('ROLE_USER');
        $this->assertFalse($isAuthenticated);

        /** @var User $user */
        $user = $this->getContainer()->get('fos_user.user_manager')->findUserByEmail('testadvertiser1@vifeed.ru');
        $this->assertNotNull($user->getPasswordRequestedAt());
        $this->assertNotNull($user->getConfirmationToken());

        return self::$client->getProfile()->getCollector('swiftmailer');
    }

    /**
     * письмо отправлено пользователю
     *
     * @depends testSendEmailOk
     */
    public function testEmailSent(MessageDataCollector $mailCollector)
    {
        $this->assertEquals(1, $mailCollector->getMessageCount());
        $collectedMessages = $mailCollector->getMessages();

        /** @var \Swift_Message $message */
        $message = $collectedMessages[0];
        $this->assertInstanceOf('Swift_Message', $message);

        return $message;
    }

    /**
     * в отправленном письме содержится нужный токен
     *
     * @depends testEmailSent
     */
    public function testSentEamilContainsRightToken(\Swift_Message $message)
    {
        /** @var User $user */
        $user = $this->getContainer()->get('fos_user.user_manager')->findUserByEmail('testadvertiser1@vifeed.ru');

        $this->assertContains('token', $message->getBody());

        preg_match('@\?token=([^\s]+)@', $message->getBody(), $matches);
        $this->assertCount(2, $matches);
        $token = $matches[1];
        $this->assertEquals($user->getConfirmationToken(), $token);
    }

    /**
     * несуществующий токен
     */
    public function testResetWrongToken()
    {
        $url = self::$router->generate('api_user_reset_password');
        $data = [
              'token' => '123567',
        ];
        self::$client->request('POST', $url, $data);
        $response = self::$client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Пользователь не найден', $content['message']);
    }

    /**
     * ошибки формы сброса пароля
     *
     * @dataProvider resetPasswordErrorsProvider
     */
    public function testResetPasswordErrors($data, $errors)
    {
        $url = self::$router->generate('api_user_reset_password');
        $data['token'] = 'abcdef12345';
        self::$client->request('POST', $url, $data);
        $response = self::$client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
        $this->validateErrors($content, $errors);
    }

    /**
     * успешный сброс пароля
     */
    public function testResetPasswordOk()
    {
        $url = self::$router->generate('api_user_reset_password');
        $data = [
              'token'     => 'abcdef6789',
              'resetting' => ['plainPassword' => [
                    'first'  => '654321',
                    'second' => '654321'
              ]]
        ];
        self::$client->request('POST', $url, $data);
        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('""', $response->getContent());

        // юзер не залогинен
        $isAuthenticated = self::$client->getContainer()->get('security.context')->isGranted('ROLE_USER');
        $this->assertFalse($isAuthenticated);
    }

    /**
     * Попытка входа со старым паролем
     * @depends testResetPasswordOk
     */
    public function testLoginWithOldPassword()
    {
        $url = self::$router->generate('sign_in');

        self::$client->request('POST', $url, [
              '_username' => 'testadvertiser3@vifeed.ru',
              '_password' => '12345',
        ]);

        $response = self::$client->getResponse();
        $this->assertEquals(401, $response->getStatusCode(), $response->getStatusCode());
    }

    /**
     * Попытка входа с новым паролем
     *
     * @depends testResetPasswordOk
     */
    public function testLoginWithNewPassword()
    {
        $url = self::$router->generate('sign_in');

        self::$client->request('POST', $url, [
              '_username' => 'testadvertiser3@vifeed.ru',
              '_password' => '654321',
        ]);

        $response = self::$client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), $response->getStatusCode());

        $this->assertTrue(self::$client->getContainer()->get('security.context')->isGranted('ROLE_USER'));
    }

    /**
     * попытка повторного сброса пароля
     *
     * @depends testResetPasswordOk
     */
    public function testResetPasswordAgain()
    {
        $url = self::$router->generate('api_user_reset_password');
        $data = [
              'token'     => 'abcdef6789',
              'resetting' => ['plainPassword' => [
                    'first'  => '754321',
                    'second' => '754321'
              ]]
        ];
        self::$client->request('POST', $url, $data);
        $response = self::$client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Пользователь не найден', $content['message']);
    }

    /**
     * data-provider для testSendEmailErrors
     */
    public function sendEmailErrorsProvider()
    {
        return [
              [
                    [], ''
              ],
              [
                    ['email' => 'testadvertiser10@vifeed.ru'],
                    'У нас нет пользователя с таким адресом'
              ],
              [
                    ['email' => 'testadvertiser10vifeed.ru'],
                    'Неверный email'
              ],
              [
                    ['email' => 'testpublisher1@vifeed.ru'],
                    'Пользователь заблокирован'
              ],
        ];
    }

    /**
     * data-provider для testResetPasswordErrors
     */
    public function resetPasswordErrorsProvider()
    {
        return [
              [
                    [],
                    ['plainPassword' => [
                          'children' => [
                                'first' => 'Пожалуйста, укажите пароль'
                          ]
                    ]]
              ],
              [
                    ['resetting' => [
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
                    ['resetting' => [
                          'plainPassword' => [
                                'first'  => 'aaa12345',
                                'second' => 'aaa22345'
                          ]]
                    ],
                    ['plainPassword' => [
                          'children' => [
                                'first' => 'Введенные пароли не совпадают'
                          ]
                    ]]
              ]
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
              ->setEnabled(true)
              ->setPlainPassword('12345');
        $userManager->updateUser($advertiser1);

        /** @var User $advertiser2 */
        $advertiser2 = $userManager->createUser();
        $advertiser2
              ->setEmail('testadvertiser2@vifeed.ru')
              ->setUsername('testadvertiser2@vifeed.ru')
              ->setBalance(100)
              ->setType(User::TYPE_ADVERTISER)
              ->setEnabled(true)
              ->setConfirmationToken('abcdef12345')
              ->setPlainPassword('12345');
        $userManager->updateUser($advertiser2);


        /** @var User $advertiser3 */
        $advertiser3 = $userManager->createUser();
        $advertiser3
              ->setEmail('testadvertiser3@vifeed.ru')
              ->setUsername('testadvertiser3@vifeed.ru')
              ->setBalance(100)
              ->setType(User::TYPE_ADVERTISER)
              ->setEnabled(true)
              ->setConfirmationToken('abcdef6789')
              ->setPlainPassword('12345');
        $userManager->updateUser($advertiser3);

        /** @var User $publisher */
        $publisher = $userManager->createUser();
        $publisher
              ->setEmail('testpublisher1@vifeed.ru')
              ->setUsername('testpublisher1@vifeed.ru')
              ->setBalance(100)
              ->setType(User::TYPE_PUBLISHER)
              ->setPlainPassword('12345');

        $userManager->updateUser($publisher);

        return [
              'users' => [$advertiser1, $advertiser2, $advertiser3, $publisher]
        ];
    }
}
 