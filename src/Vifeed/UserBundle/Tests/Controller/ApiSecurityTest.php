<?php

namespace Vifeed\UserBundle\Tests\Controller;

use Vifeed\SystemBundle\Tests\ApiTestCase;

class ApiSecurityTest extends ApiTestCase
{

    /**
     * Новый юзер
     *
     * @param array $data
     * @param int   $code
     * @param null  $errors
     * @param array $parameters
     *
     * @dataProvider putUsersProvider
     *
     * todo: если рекламодатель зарегался, то он автоматом авторизуется, и у него должен быть токен
     */
    public function testPutUsers($data, $code, $errors = null, $parameters = array())
    {
        self::$client->restart();
        $url = self::$router->generate('api_put_users');

        self::$client->request('GET', '/'); // чтобы открыть сессию
        self::$client->enableProfiler();

        $csrf = self::$client->getContainer()->get('form.csrf_provider');
        $token = $csrf->generateCsrfToken('registration');
        $key = array_keys($data)[0];
//        $data[$key]['_token'] = $token;

        self::$client->request('PUT', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals($code, $response->getStatusCode());

        $isAuthenticated = self::$client->getContainer()->get('security.context')->isGranted('ROLE_USER');

        if ($errors !== null) {
            $this->assertJson($response->getContent());
            $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
            $this->assertArrayHasKey('errors', $content);
            $this->assertArrayHasKey('children', $content['errors']);
            $this->validateErros($content, $errors);
            $this->assertFalse($isAuthenticated);
        }
        if ($code == 201) {
            $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
            $this->assertTrue(is_array($content));
            $mailCollector = self::$client->getProfile()->getCollector('swiftmailer');

            $this->assertEquals(1, $mailCollector->getMessageCount());
            $collectedMessages = $mailCollector->getMessages();
            /** @var \Swift_Message $message */
            $message = $collectedMessages[0];
            $this->assertInstanceOf('Swift_Message', $message);
            if (in_array('confirm', $parameters)) {
                preg_match('@\/confirm\/([^"]+)"@', $message->getBody(), $matches);
                $token = $matches[1];
                $this->testConfirmation($token);
            }
            if (array_key_exists('advertiser_registration', $data)) {
                $this->assertTrue($isAuthenticated);
            } else {
                $this->assertFalse($isAuthenticated);
            }
        }
    }

    /**
     * Тест логина
     *
     * @param array $data
     * @param int   $code
     * @param null  $errors
     *
     * @dataProvider testLoginProvider
     *
     * todo: тест входа рекламодателя (как?)
     * todo: получение токена для апи - проверить
     */
    public function testLogin($data, $code, $errors = null)
    {
        self::$client->restart();

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
            $this->assertEquals(false, $content['success']);
            $this->assertArrayHasKey('message', $content);
            $this->assertEquals($errors, $content['message']);
            $this->assertNull(self::$client->getContainer()->get('security.context')->getToken());
        } else {
            $this->assertEquals(true, $content['success']);
            $this->assertTrue(self::$client->getContainer()->get('security.context')->isGranted('ROLE_USER'));
        }

    }


    /**
     * тест подтвреждения емейла. Может вызываться из регистрации с конкретным токеном
     *
     * @param string $token
     */
    public function testConfirmation($token = '')
    {
        $url = self::$router->generate('api_patch_users_confirm');

        $data = array();
        $data['token'] = $token ?: 'token';

        self::$client->request('PATCH', $url, $data);

        $content = self::$client->getResponse()->getContent();
        if ($token !== '') {
            $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
        } else {
            $this->assertEquals(404, self::$client->getResponse()->getStatusCode());
        }
    }

    /**
     * logout и удаление api-токена
     */
    public function testDeleteToken()
    {
        self::$client->restart();

        $url = self::$router->generate('api_delete_users_token');

        self::$client->request('DELETE', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());

        // запрос для авторизации
        $this->sendRequest('GET', self::$router->generate('api_get_tags', array('word' => 'word')));

        $userId = self::$client->getContainer()->get('security.context')->getToken()->getUser()->getId();
        $tokenManager = self::$client->getContainer()->get('vifeed.user.wsse_token_manager');

        $this->assertNotNull($tokenManager->getUserToken($userId));

        $this->sendRequest('DELETE', $url);

        $this->assertEquals(204, self::$client->getResponse()->getStatusCode());
        $this->assertNull($tokenManager->getUserToken($userId));

        // теперь мы не можем получить доступ
        $this->sendRequest('GET', self::$router->generate('api_get_tags', array('word' => 'word')));
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
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
                        'email' => 'advregtest1@test.test'
                    )
                ),
                201,
            ),
            array(
                array(
                    'publisher_registration' => array(
                        'email' => 'advregtest1@test.test'
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
                        'email' => 'publisheregtest1@test.test',
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
                        'email' => 'publisheregtest1@test.test',
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
                        'email' => 'publisheregtest1@test.test',
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
                        'email' => 'publisheregtest1@test.test',
                        'plainPassword' => array(
                            'first' => 'aaabbb',
                            'second' => 'aaabbb'
                        )
                    )
                ),
                201,
            ),
            array(
                array(
                    'publisher_registration' => array(
                        'email' => 'publisheregtest2@test.test',
                        'plainPassword' => array(
                            'first' => 'aaabbb',
                            'second' => 'aaabbb'
                        )
                    )
                ),
                201,
                null,
                array('confirm')
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
                    '_username' => 'publisheregtest1@test.test',
                    '_password' => 'aaabbb',
                ),
                401,
                'User account is disabled.'
            ),
            array(
                array(
                    '_username' => 'publisheregtest2@test.test',
                    '_password' => 'aaabbb',
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
