<?php

namespace Vifeed\CampaignBundle\Tests\Controller;

use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\CampaignBundle\Form\CampaignType;
use Vifeed\CampaignBundle\Tests\ApiTestCase;

class CampaignControllerTest extends ApiTestCase
{

    private static $createdCampaignId = null;

    /**
     * Новая кампания
     *
     * @dataProvider putCampaignsProvider
     *
     */
    public function testPutCampaigns($data, $code, $errors = null)
    {
        $url = self::$router->generate('api_put_campaigns');
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
            static::$createdCampaignId = $content['id'];
        }
    }


    /**
     * Список кампаний
     * todo: сделать проверку выдаваемой информации после утверждения формата
     */
    public function testGetCampaigns()
    {
        $url = self::$router->generate('api_get_campaigns');
        $this->sendRequest('GET', $url);
        $response = self::$client->getResponse();
        $content = $response->getContent();

        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
        $this->assertJson($content);

        $data = json_decode($content, JSON_UNESCAPED_UNICODE);
        $this->assertTrue(is_array($data));
        $this->assertTrue(sizeof($data) > 0);
    }

    /**
     * Кампания по id
     *
     * @//depends testPutCampaigns
     *
     * todo: проверку выдаваемой информации после утверждения формата
     */
    public function testGetCampaign()
    {
        $url = self::$router->generate('api_get_campaign', array('id' => -1));
        $this->sendRequest('GET', $url);
        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());

        $id = static::$createdCampaignId;
        $this->assertTrue(is_numeric($id));

        $url = self::$router->generate('api_get_campaign', array('id' => $id));
        $this->sendRequest('GET', $url);
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        $content = self::$client->getResponse()->getContent();
        $this->assertJson($content);

        $data = json_decode($content, JSON_UNESCAPED_UNICODE);

        $this->assertTrue(is_array($data));
        $this->assertArrayHasKey('campaign', $data);
        $this->assertArrayHasKey('id', $data['campaign']);
        $this->assertEquals($id, $data['campaign']['id']);
    }


    /**
     * Редактировать кампанию
     *
     * todo: добавить проверку на кейсы по различиям создания и редактирования кампании
     */
    public function testPutCampaign()
    {
        $url = self::$router->generate('api_put_campaign', array('id' => -1));
        $this->sendRequest('PUT', $url);
        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());

        $id = static::$createdCampaignId;
        $this->assertTrue(is_numeric($id));

        $data = array(
            'campaign' => array('maxBid' => 11)
        );
        $url = self::$router->generate('api_put_campaign', array('id' => $id));
        $this->sendRequest('PUT', $url, $data);

        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        $url = self::$router->generate('api_get_campaign', array('id' => $id));
        $this->sendRequest('GET', $url);
        $data = json_decode(self::$client->getResponse()->getContent(), JSON_UNESCAPED_UNICODE);
        $this->assertEquals(11, $data['campaign']['max_bid']);
    }

    /**
     * Удаление кампании
     */
    public function testDeleteCampaign()
    {
        $url = self::$router->generate('api_delete_campaign', array('id' => -1));
        $this->sendRequest('DELETE', $url);
        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());

        $id = static::$createdCampaignId;
        $this->assertTrue(is_numeric($id));

        $url = self::$router->generate('api_delete_campaign', array('id' => $id));
        $this->sendRequest('DELETE', $url);
        $this->assertEquals(204, self::$client->getResponse()->getStatusCode());

        $url = self::$router->generate('api_get_campaign', array('id' => $id));
        $this->sendRequest('GET', $url);
        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());
    }


    /**
     * data-provider для testPutCampaigns
     * @return array
     */
    public function putCampaignsProvider()
    {
        $data = array(
            array(
                array(),
                400,
                array(
                    'name' => 'Название не должно быть пустым',
                )
            ),
            array(
                array(
                    'campaign' => array(
                        'name'       => 'test1',
                        'gender'     => 'aa',
                        'maxBid'     => 10,
                        'budgetType' => 'aa'
                    )
                ),
                400,
                array(
                    'gender'     => 'Выберите пол',
                    'budgetType' => 'Выберите тип бюджета',
                )
            ),
            array(
                array(
                    'campaign' => array(
                        'name'       => 'test1',
                        'gender'     => 'male',
                        'maxBid'     => 10,
                        'budget'     => 100,
                        'budgetType' => 'lifetime',
                        'bid'        => 10,
                        'startAt'    => array(
                            'date' => array(
                                'day'   => 1,
                                'month' => 10,
                                'year'  => 2013
                            ),
                            'time' => array(
                                'hour'   => 0,
                                'minute' => 0
                            )
                        )
                    )
                ),
                201,
            ),
        );

        return $data;
    }

}
