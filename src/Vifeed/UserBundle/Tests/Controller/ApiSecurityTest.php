<?php

namespace Vifeed\UserBundle\Tests\Controller;

use Vifeed\SystemBundle\Tests\ApiTestCase;
use Vifeed\SystemBundle\Tests\TestCase;

class ApiSecurityTest extends ApiTestCase
{

    public static function tearDownAfterClass()
    {
        $um = static::createClient()->getContainer()->get('fos_user.user_manager');
        foreach (array('regtest@test.test', 'regtest2@test.test') as $email) {
            $user = $um->findUserByEmail($email);
            if ($user) {
                $um->deleteUser($user);
            }
        }

        parent::tearDownAfterClass();

    }

    /**
     * Новый юзер
     *
     * todo: сделать проверку активации юзера и подтверждения почты
     *
     * @dataProvider putUsersProvider
     */
    public function testPutUsers($data, $code, $errors = null)
    {
        $url = self::$router->generate('api_put_user_register');

        self::$client->request('GET', '/'); // чтобы открыть сессию
        self::$client->enableProfiler();

        $csrf = self::$client->getContainer()->get('form.csrf_provider');
        $token = $csrf->generateCsrfToken('registration');
        $key = array_keys($data)[0];
        $data[$key]['_token'] = $token;

        self::$client->request('PUT', $url, $data);
        if (self::$client->getResponse()->getStatusCode() != $code) {
            var_dump(
                json_decode(self::$client->getResponse()->getContent(), JSON_UNESCAPED_UNICODE)['errors']['children']
            );

        }
        $this->assertEquals($code, self::$client->getResponse()->getStatusCode());

        $response = self::$client->getResponse();

        if ($errors !== null) {
            $this->assertJson($response->getContent());
            $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
            $this->assertArrayHasKey('errors', $content);
            $this->assertArrayHasKey('children', $content['errors']);
            $this->validateErros($content, $errors);
        }
        if ($code == 201) {
            $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
            $this->assertTrue(is_array($content));
            $mailCollector = self::$client->getProfile()->getCollector('swiftmailer');

            $this->assertEquals(1, $mailCollector->getMessageCount());
            $collectedMessages = $mailCollector->getMessages();
            $message = $collectedMessages[0];
            $this->assertInstanceOf('Swift_Message', $message);
        }
    }

    /**
     * Тест логина
     *
     * @dataProvider testLoginProvider
     */
    public function testLogin($data, $code, $errors = null)
    {
        $url = self::$router->generate('api_fos_user_security_check');
        self::$client->request('GET', '/'); // чтобы открыть сессию

        $csrf = self::$client->getContainer()->get('form.csrf_provider');
        $token = $csrf->generateCsrfToken('authenticate');

        $data['_csrf_token'] = $token;

        self::$client->request('POST', $url, $data);

        $this->assertEquals($code, self::$client->getResponse()->getStatusCode());

        $content = self::$client->getResponse()->getContent();
        $this->assertJson($content);

        $content = json_decode($content, JSON_UNESCAPED_UNICODE);
        $this->assertArrayHasKey('success', $content);

        if ($errors !== null) {
            $this->assertEquals($content['success'], false);
            $this->assertArrayHasKey('message', $content);
            $this->assertEquals($content['message'], $errors);
        } else {
            $this->assertEquals($content['success'], true);
        }

    }

    /**
     * data-provider для testPutUsers
     * @return array
     */
    public function putUsersProvider()
    {
        $data = array(
            array(
                array(
                    'advertiser_registration' => array(
                        'email' => 'afasfdsa'
                    )
                ),
                400,
                array(
                    'email' => 'Email в неправильном формате',
                )
            ),
            array(
                array(
                    'advertiser_registration' => array(
                        'email' => 'regtest@test.test'
                    )
                ),
                201,
            ),
            array(
                array(
                    'publisher_registration' => array(
                        'email' => 'regtest@test.test'
                    )
                ),
                400,
                array(
                    'email' => 'Email уже используется',
                )
            ),
            array(
                array(
                    'publisher_registration' => array(
                        'email' => 'regtest2@test.test',
                    )
                ),
                400,
                array(
                    'plainPassword' => array(
                        'children' => array(
                            'first' => 'Пожалуйста, укажите пароль'
                        )
                    ),
                )
            ),
            array(
                array(
                    'publisher_registration' => array(
                        'email' => 'regtest2@test.test',
                        'plainPassword' => array(
                            'first' => 'aaa'
                        )
                    )
                ),
                400,
                array(
                    'plainPassword' => array(
                        'children' => array(
                            'first' => 'Пароли должны совпадать!'
                        )
                    ),
                )
            ),
            array(
                array(
                    'publisher_registration' => array(
                        'email' => 'regtest2@test.test',
                        'plainPassword' => array(
                            'first' => 'aaa',
                            'second' => 'aaa'
                        )
                    )
                ),
                400,
                array(
                    'plainPassword' => array(
                        'children' => array(
                            'first' => 'Пароль слишком короткий'
                        )
                    ),
                )

            ),
            array(
                array(
                    'publisher_registration' => array(
                        'email' => 'regtest2@test.test',
                        'plainPassword' => array(
                            'first' => 'aaabbb',
                            'second' => 'aaabbb'
                        )
                    )
                ),
                201,
            ),
        );

        return $data;
    }

    /**
     * data-provider для testLogin
     * @return array
     */
    public function testLoginProvider()
    {
        $data = array(
            array(
                array(
                    '_username' => 'test',
                    '_password' => 'test',
                ),
                200,
            ),
            array(
                array(
                    '_username' => 'test',
                    '_password' => 'test1',
                ),
                401,
                'Bad credentials'
            ),
        );

        return $data;
    }

}
