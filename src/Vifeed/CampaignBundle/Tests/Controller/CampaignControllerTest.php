<?php

namespace Vifeed\CampaignBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\CampaignBundle\Form\CampaignType;

class CampaignControllerTest extends WebTestCase
{
    /** @var \Symfony\Bundle\FrameworkBundle\Client */
    private $client;

    /** @var \Symfony\Component\Routing\Router */
    private $router;

    private static $createdCampaignId = null;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->router = $this->client->getContainer()->get('router');
    }

    /**
     * Новая кампания
     *
     * @dataProvider putCampaignsProvider
     *
     */
    public function testPutCampaigns($data, $code, $errors = null)
    {
        $url = $this->router->generate('api_put_campaigns');
        $this->client->request('PUT', $url, $data);
        $this->assertEquals($code, $this->client->getResponse()->getStatusCode());

        $response = $this->client->getResponse();

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
        $url = $this->router->generate('api_get_campaigns');
        $crawler = $this->client->request('GET', $url);
        $response = $this->client->getResponse();
        $content = $response->getContent();

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
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
        $url = $this->router->generate('api_get_campaign', array('id' => -1));
        $this->client->request('GET', $url);
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());

        $id = static::$createdCampaignId;
        $this->assertTrue(is_numeric($id));

        $url = $this->router->generate('api_get_campaign', array('id' => $id));
        $this->client->request('GET', $url);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = $this->client->getResponse()->getContent();
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
        $url = $this->router->generate('api_put_campaign', array('id' => -1));
        $this->client->request('PUT', $url);
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());

        $id = static::$createdCampaignId;
        $this->assertTrue(is_numeric($id));

        $data = array(
            'campaign' => array('maxBid' => 11)
        );
        $url = $this->router->generate('api_put_campaign', array('id' => $id));
        $this->client->request('PUT', $url, $data);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $url = $this->router->generate('api_get_campaign', array('id' => $id));
        $this->client->request('GET', $url);
        $data = json_decode($this->client->getResponse()->getContent(), JSON_UNESCAPED_UNICODE);
        $this->assertEquals(11, $data['campaign']['max_bid']);
    }

    /**
     * Удаление кампании
     */
    public function testDeleteCampaign()
    {
        $url = $this->router->generate('api_delete_campaign', array('id' => -1));
        $this->client->request('DELETE', $url);
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());

        $id = static::$createdCampaignId;
        $this->assertTrue(is_numeric($id));

        $url = $this->router->generate('api_delete_campaign', array('id' => $id));
        $this->client->request('DELETE', $url);
        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());

        $url = $this->router->generate('api_get_campaign', array('id' => $id));
        $this->client->request('GET', $url);
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
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
