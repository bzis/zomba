<?php

namespace Vifeed\UserBundle\Tests\Controller;

use Vifeed\SystemBundle\Tests\ApiTestCase;
use Vifeed\SystemBundle\Tests\TestCase;

class ApiSecurityTest extends ApiTestCase
{

    public static function tearDownAfterClass()
    {
        $um = static::createClient()->getContainer()->get('fos_user.user_manager');
        $user = $um->findUserByEmail('regtest@test.test');
        if ($user) {
            $um->deleteUser($user);
        }

        parent::tearDownAfterClass();

    }

    /**
     * Новый юзер
     *
     * @dataProvider putUsersProvider
     */
    public function testPutUsers($data, $code, $errors = null)
    {
        $url = self::$router->generate('api_put_user_register');

        self::$client->request('PUT', $url); // чтобы открыть сессию

        $csrf = self::$client->getContainer()->get('form.csrf_provider');
        $token = $csrf->generateCsrfToken('registration');

        $data['registration']['_token'] = $token;

        self::$client->request('PUT', $url, $data);
        $this->assertEquals($code, self::$client->getResponse()->getStatusCode());

        $response = self::$client->getResponse();

        if ($errors !== null) {
            $this->assertJson($response->getContent());
            $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
            $this->assertArrayHasKey('errors', $content);
            $this->assertArrayHasKey('children', $content['errors']);
            foreach ($errors as $field => $error) {
                $this->assertArrayHasKey($field, $content['errors']['children']);
                $this->assertArrayHasKey('errors', $content['errors']['children'][$field]);
                $this->assertTrue(in_array($error, $content['errors']['children'][$field]['errors']));
            }
        }
        if ($code == 201) {
            $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
            $this->assertTrue(is_array($content));
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
        self::$client->request('POST', $url); // чтобы открыть сессию

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
                    'registration' => array(
                        'type' => 'advertiser',
                    )
                ),
                400,
                array(
                    'email' => 'Пожалуйста, укажите Ваш email',
                )
            ),
            array(
                array(
                    'registration' => array(
                        'type'  => 'test1',
                        'email' => 'afasfdsa'
                    )
                ),
                400,
                array(
                    'type'  => 'Выберите тип',
                    'email' => 'Email в неправильном формате',
                )
            ),
            array(
                array(
                    'registration' => array(
                        'type'  => 'advertiser',
                        'email' => 'regtest@test.test'
                    )
                ),
                201,
            ),
            array(
                array(
                    'registration' => array(
                        'type'  => 'publisher',
                        'email' => 'regtest@test.test'
                    )
                ),
                400,
                array(
                    'email' => 'Email уже используется',
                )
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
