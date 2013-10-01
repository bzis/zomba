<?php

namespace Vifeed\UserBundle\Tests\Controller;

use Vifeed\SystemBundle\Tests\TestCase;

class ApiSecurityTest extends TestCase
{

    public static function tearDownAfterClass()
    {
        $um = static::createClient()->getContainer()->get('fos_user.user_manager');
        $user = $um->findUserByEmail('test@test.test');
        $um->deleteUser($user);

        parent::tearDownAfterClass();

    }

    /**
     * Новый юзер
     *
     * @dataProvider putUsersProvider
     *
     */
    public function testPutUsers($data, $code, $errors = null)
    {
        $url = self::$router->generate('api_put_user_register');
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
                        'email' => 'test@test.test'
                    )
                ),
                201,
            ),
            array(
                array(
                    'registration' => array(
                        'type'  => 'publisher',
                        'email' => 'test@test.test'
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
}
