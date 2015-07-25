<?php

namespace Vifeed\PlatformBundle\Tests\Controller;

use Vifeed\PlatformBundle\Entity\Platform;

/**
 * Class PlatformControllerCreatePlatformTest
 *
 * @package Vifeed\PlatformBundle\Tests\Controller
 */
class PlatformControllerCreatePlatformTest extends PlatformControllerTestCase
{
    /**
     * попытка создания площадки без авторизации
     */
    public function testCreatePlatformUnauthorized()
    {
        $url = self::$router->generate('api_put_platforms');
        self::$client->request('PUT', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * попытка создания площадки рекламодателем
     */
    public function testCreatePlatformWithAdvertiser()
    {
        $url = self::$router->generate('api_put_platforms');
        $this->sendRequest(self::$testAdvertiser, 'PUT', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * ошибки при создании площадки
     *
     * @param array $data
     * @param array $errors
     *
     * @dataProvider createPlatformErrorsProvider
     */
    public function testCreatePlatformErrors($data, $errors)
    {
        $url = self::$router->generate('api_put_platforms');

        $this->sendRequest(self::$testPublisher, 'PUT', $url, $data);
        $response = self::$client->getResponse();

        $this->assertEquals(400, $response->getStatusCode(), $response->getContent());

        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->validateErrors($content, $errors);
    }

    /**
     * успешное создание площадки
     */
    public function testCreatePlatformOk()
    {
        $url = self::$router->generate('api_put_platforms');

        $data = [
              'platform' => [
                    'name'        => 'test1',
                    'description' => 'aa',
                    'url'         => 'ok.ru',
                    'type'        => 'site'
              ]
        ];

        $dateTime = new \DateTime();
        $this->sendRequest(self::$testPublisher, 'PUT', $url, $data);
        $response = self::$client->getResponse();

        $content = $response->getContent();
        $this->assertEquals(201, $response->getStatusCode(), $content);

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
        $this->assertInternalType('array', $content);

        $this->assertEquals($content['created_at'], $content['updated_at']);
        $createdAt = \DateTime::createFromFormat(\DateTime::ISO8601, $content['created_at'])->getTimestamp();
        $this->assertLessThanOrEqual(2, time() - $createdAt);
    }

    /**
     * успешное создание площадки VK
     */
    public function testCreateVkPlatformOk()
    {
        $url = self::$router->generate('api_put_platforms');

        $data = [
              'platform' => [
                    'name'        => 'test1',
                    'description' => 'aa',
                    'url'         => 'vk.com/test',
                    'type'        => 'vk',
                    'vkId'        => '12356'
              ]
        ];

        $this->sendRequest(self::$testPublisher, 'PUT', $url, $data);
        $response = self::$client->getResponse();

        $content = $response->getContent();
        $this->assertEquals(201, $response->getStatusCode(), $content);

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
        $this->assertInternalType('array', $content);

        $platform = $this->getEntityManager()->find('VifeedPlatformBundle:Platform', $content['id']);
        $this->assertInstanceOf('Vifeed\PlatformBundle\Entity\VkPlatform', $platform);
    }

    /**
     * создание площадки с такими же данными, как уже удалённая
     */
    public function testCreatePlatformLikeDeletedOne()
    {
        $url = self::$router->generate('api_put_platforms');

        $data = [
              'platform' => [
                    'name'        => 'name4',
                    'description' => '333',
                    'url'         => 'vk.com/12379',
                    'type'        => 'vk',
                    'vkId'        => '12379'
              ]
        ];

        $this->sendRequest(self::$testPublisher, 'PUT', $url, $data);
        $response = self::$client->getResponse();

        $content = $response->getContent();
        $this->assertEquals(201, $response->getStatusCode(), $content);
    }

    /**
     * data-provider для testCreatePlatformErrors
     *
     * @return array
     */
    public function createPlatformErrorsProvider()
    {
        $data = [
              [
                    [],
                    ['name'        => 'Название не должно быть пустым',
                     'description' => 'Описание не должно быть пустым',
                     'url'         => 'Адрес не должен быть пустым',
                     'type'        => 'Значение не должно быть пустым.']
              ],
              [
                    ['platform' => [
                          'name'        => 'test1',
                          'description' => 'aa',
                          'url'         => 'test1',
                          'type'        => 'aaa'
                    ]],
                    ['url'  => 'Адрес должен быть валидным',
                     'type' => 'Неправильный тип площадки']
              ],
              [
                    ['platform' => [
                          'name'        => 'test1',
                          'description' => 'aa',
                          'url'         => 'ya.ru',
                          'type'        => 'site'
                    ]],
                    ['url' => 'Площадка с таким адресом уже зарегистрирована в системе']
              ],
              [
                    ['platform' => [
                          'name'        => 'test1',
                          'description' => 'aa',
                          'url'         => 'http://ya.ru',
                          'type'        => 'site'
                    ]],
                    ['url' => 'Площадка с таким адресом уже зарегистрирована в системе']
              ],
              [
                    ['platform' => [
                          'name'        => 'test1',
                          'description' => 'aa',
                          'url'         => 'vk.com/test',
                          'type'        => 'vk'
                    ]],
                    ['vkId' => 'Значение не должно быть пустым.']
              ],
              [
                    ['platform' => [
                          'name'        => 'test1',
                          'description' => 'aa',
                          'url'         => 'vk.com/test',
                          'type'        => 'vk',
                          'vkId'        => 'a12432'
                    ]],
                    ['vkId' => 'Значение недопустимо.']
              ],
              [
                    ['platform' => [
                          'name'        => 'test1',
                          'description' => 'aa',
                          'url'         => 'vk.com/12377',
                          'type'        => 'vk',
                          'vkId'        => '12432'
                    ]],
                    ['url' => 'Площадка с таким адресом уже зарегистрирована в системе']
              ],
              [
                    ['platform' => [
                          'name'        => 'test1',
                          'description' => 'aa',
                          'url'         => 'vk.com/12378',
                          'type'        => 'platform',
                    ]],
                    ['url' => 'Тип площадки не соответствует адресу']
              ],
              [
                    ['platform' => [
                          'name'        => 'test1',
                          'description' => 'aa',
                          'url'         => 'nevk.com/12378',
                          'type'        => 'vk',
                    ]],
                    ['url' => 'Тип площадки не соответствует адресу']
              ],
        ];

        return $data;
    }
}
