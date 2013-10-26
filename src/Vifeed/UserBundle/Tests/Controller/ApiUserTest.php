<?php

namespace Vifeed\UserBundle\Tests\Controller;

use Vifeed\SystemBundle\Tests\ApiTestCase;

class ApiUserTest extends ApiTestCase
{

    /**
     * User по id
     *
     * todo: проверку выдаваемой информации после утверждения формата
     */
    public function testGetUser()
    {
        $url = self::$router->generate('api_get_user', array('id' => -1));

        self::$client->request('GET', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());

        $this->sendRequest('GET', $url);
        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());

        $url = self::$router->generate('api_get_user', array('id' => self::$user->getId()));
        $this->sendRequest('GET', $url);
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        $content = self::$client->getResponse()->getContent();
        $this->assertJson($content);

        $data = json_decode($content, JSON_UNESCAPED_UNICODE);

        $this->assertTrue(is_array($data));
        $this->assertArrayHasKey('user', $data);
        $this->assertArrayHasKey('id', $data['user']);
        $this->assertEquals(self::$user->getId(), $data['user']['id']);
    }

}
