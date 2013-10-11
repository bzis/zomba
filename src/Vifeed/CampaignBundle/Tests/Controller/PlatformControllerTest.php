<?php

namespace Vifeed\CampaignBundle\Tests\Controller;

use Vifeed\CampaignBundle\Entity\Platform;
use Vifeed\CampaignBundle\Form\PlatformType;
use Vifeed\SystemBundle\Tests\ApiTestCase;

class PlatformControllerTest extends ApiTestCase
{

    /**
     * Новая площадка
     *
     * @dataProvider putPlatformsProvider
     *
     */
    public function testPutPlatforms($data, $code, $errors = null)
    {
        $url = self::$router->generate('api_put_platforms');
        self::$client->request('PUT', $url, $data);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());

        $this->sendRequest('PUT', $url, $data);
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
            static::$parameters['created_id'] = $content['id'];
        }
    }


    /**
     * Площадка по id
     *
     * todo: проверку выдаваемой информации после утверждения формата
     */
    public function testGetPlatform()
    {
        $url = self::$router->generate('api_get_platform', array('id' => -1));

        self::$client->request('GET', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());

        $this->sendRequest('GET', $url);
        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());

        $this->assertArrayHasKey('created_id', static::$parameters);
        $id = static::$parameters['created_id'];
        $this->assertTrue(is_numeric($id));

        $url = self::$router->generate('api_get_platform', array('id' => $id));
        $this->sendRequest('GET', $url);
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        $content = self::$client->getResponse()->getContent();
        $this->assertJson($content);

        $data = json_decode($content, JSON_UNESCAPED_UNICODE);

        $this->assertTrue(is_array($data));
        $this->assertArrayHasKey('platform', $data);
        $this->assertArrayHasKey('id', $data['platform']);
        $this->assertEquals($id, $data['platform']['id']);
    }


    /**
     * Редактировать площадку
     *
     * todo: добавить проверку на кейсы по различиям создания и редактирования площадки
     */
    public function testPutPlatform()
    {
        $url = self::$router->generate('api_put_platform', array('id' => -1));

        self::$client->request('PUT', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());

        $this->sendRequest('PUT', $url);
        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());

        $this->assertArrayHasKey('created_id', static::$parameters);
        $id = static::$parameters['created_id'];
        $this->assertTrue(is_numeric($id));

        $data = array(
            'platform' => array('name' => 'test2')
        );
        $url = self::$router->generate('api_put_platform', array('id' => $id));
        $this->sendRequest('PUT', $url, $data);

        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        $url = self::$router->generate('api_get_platform', array('id' => $id));
        $this->sendRequest('GET', $url);
        $data = json_decode(self::$client->getResponse()->getContent(), JSON_UNESCAPED_UNICODE);
        $this->assertEquals('test2', $data['platform']['name']);
    }

    /**
     * Удаление кампании
     */
    public function testDeleteCampaign()
    {
        $url = self::$router->generate('api_delete_platform', array('id' => -1));

        self::$client->request('DELETE', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());

        $this->sendRequest('DELETE', $url);
        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());

        $this->assertArrayHasKey('created_id', static::$parameters);
        $id = static::$parameters['created_id'];
        $this->assertTrue(is_numeric($id));

        $url = self::$router->generate('api_delete_platform', array('id' => $id));
        $this->sendRequest('DELETE', $url);
        $this->assertEquals(204, self::$client->getResponse()->getStatusCode());

        $url = self::$router->generate('api_get_platform', array('id' => $id));
        $this->sendRequest('GET', $url);
        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());
    }


    /**
     * data-provider для testPutPlatforms
     * @return array
     */
    public function putPlatformsProvider()
    {
        $data = array(
            array(
                array(),
                400,
                array(
                    'name'       => 'Название не должно быть пустым',
                    'description' => 'Описание не должно быть пустым',
                    'url'        => 'Адрес не должен быть пустым',
                )
            ),
            array(
                array(
                    'platform' => array(
                        'name'       => 'test1',
                        'description' => 'aa',
                        'url'        => 'test1',
                    )
                ),
                201,
            ),
        );

        return $data;
    }

}
