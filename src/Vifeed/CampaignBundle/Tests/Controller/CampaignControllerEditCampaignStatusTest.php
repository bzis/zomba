<?php

namespace Vifeed\CampaignBundle\Tests\Controller;

use PhpAmqpLib\Message\AMQPMessage;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\UserBundle\Entity\User;

/**
 * Class CampaignControllerEditCampaignStatusTest
 *
 * @package Vifeed\CampaignBundle\Tests\Controller
 */
class CampaignControllerEditCampaignStatusTest extends CampaignControllerTestCase
{

    /**
     * изменение статуса кампании старым способом - через основной метод изменения кампании
     */
    public function testEditCampaignStatusMainForm()
    {
        $advertiser = self::$parameters['fixtures']['advertisers'][0];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][0];

        $url = self::$router->generate('api_put_campaign', array('id' => $campaign->getId()));

        $data = ['campaign' => ['status' => Campaign::STATUS_PAUSED]];
        $this->sendRequest($advertiser, 'PUT', $url, $data);
        $response = self::$client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());

        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->validateErrors($content, ['Эта форма не должна содержать дополнительных полей.']);
    }

    /**
     * пытаемся установить несуществующий статус
     */
    public function testSetWrongStatus1()
    {
        $advertiser = self::$parameters['fixtures']['advertisers'][0];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][0];

        $url = self::$router->generate('api_put_campaign_status', array('id' => $campaign->getId()));

        $data = ['campaign' => ['status' => 'aaa']];
        $this->sendRequest($advertiser, 'PUT', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());

        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->validateErrors($content, ['status' => 'Выберите статус']);
    }

    /**
     * пытаемся установить некорректный статус
     */
    public function testSetWrongStatus2()
    {
        $advertiser = self::$parameters['fixtures']['advertisers'][0];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][0];

        $url = self::$router->generate('api_put_campaign_status', array('id' => $campaign->getId()));

        $data = ['campaign' => ['status' => 'awaiting']];
        $this->sendRequest($advertiser, 'PUT', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());

        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->validateErrors($content, ['status' => 'Выберите статус']);
    }

    /**
     * пытаемся поменять статус на ON при недостатке средств
     */
    public function testSetStatusOnErrByCampaignBalance()
    {
        $advertiser = self::$parameters['fixtures']['advertisers'][1];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][4];

        $url = self::$router->generate('api_put_campaign_status', array('id' => $campaign->getId()));

        $data = ['campaign' => ['status' => Campaign::STATUS_ON]];
        $this->sendRequest($advertiser, 'PUT', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());

        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->validateErrors($content, ['status' => 'Недостаточно средств для запуска кампании']);
    }

    /**
     * пытаемся поменять статус с ended на ON при недостатке средств
     */
    public function testSetStatusOnErrByUserBalance()
    {
        $advertiser = self::$parameters['fixtures']['advertisers'][3];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][8];

        $url = self::$router->generate('api_put_campaign_status', array('id' => $campaign->getId()));

        $data = ['campaign' => ['status' => Campaign::STATUS_ON]];
        $this->sendRequest($advertiser, 'PUT', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());

        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->validateErrors($content, ['status' => 'Недостаточно свободных средств для запуска кампании']);
    }

    /**
     * пытаемся поменять статус с AWAITING на ON при недостатке средств
     */
    public function testSetStatusOnErrByDailyBudget()
    {
        $advertiser = self::$parameters['fixtures']['advertisers'][1];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][3];

        $url = self::$router->generate('api_put_campaign_status', array('id' => $campaign->getId()));

        $data = ['campaign' => ['status' => Campaign::STATUS_ON]];
        $this->sendRequest($advertiser, 'PUT', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());

        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->validateErrors($content, ['status' => 'Дневной бюджет кампании на сегодня исчерпан']);
    }

    /**
     * пытаемся поменять статус с на ON с ARCHIVED
     */
    public function testSetStatusOn()
    {
        /** @var User $advertiser */
        $advertiser = self::$parameters['fixtures']['advertisers'][1];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][5];

        self::$em->refresh($advertiser);
        $userBalanceBefore = $advertiser->getBalance();
        $this->assertEquals(0, $campaign->getBalance());

        $url = self::$router->generate('api_put_campaign_status', array('id' => $campaign->getId()));

        $data = ['campaign' => ['status' => Campaign::STATUS_ON]];
        $this->sendRequest($advertiser, 'PUT', $url, $data);

        $response = self::$client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        self::$em->refresh($campaign);
        self::$em->refresh($advertiser);

        $this->assertEquals(Campaign::STATUS_ON, $campaign->getStatus());

        // зачислились деньги на баланс и списались с юзера
        $this->assertEquals(60, $campaign->getBalance());
        $this->assertEquals($userBalanceBefore - 60, $advertiser->getBalance());

        // поменялось время старта
        $this->assertLessThanOrEqual(3, time() - $campaign->getStartAt()->getTimestamp());
        // поменялось время редактирования
        $this->assertLessThanOrEqual(3, time() - $campaign->getUpdatedAt()->getTimestamp());
    }

    /**
     * пытаемся остановить только что запущенную кампанию
     */
    public function testPauseJustStartedCampaign()
    {
        /** @var User $advertiser */
        $advertiser = self::$parameters['fixtures']['advertisers'][1];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][2];

        $url = self::$router->generate('api_put_campaign_status', array('id' => $campaign->getId()));

        $data = ['campaign' => ['status' => Campaign::STATUS_PAUSED]];
        $this->sendRequest($advertiser, 'PUT', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());

        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->validateErrors($content, ['status' => 'Невозможно остановить кампанию в течение двух часов после запуска. Вы сможете остановить кампанию через 120 мин.']);
    }

    /**
     * останавливаем запущенную кампанию
     */
    public function testPauseCampaign()
    {
        /** @var User $advertiser */
        $advertiser = self::$parameters['fixtures']['advertisers'][0];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][0];

        $url = self::$router->generate('api_put_campaign_status', array('id' => $campaign->getId()));

        $data = ['campaign' => ['status' => Campaign::STATUS_PAUSED]];
        $this->sendRequest($advertiser, 'PUT', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        self::$em->refresh($campaign);
        $this->assertEquals(10, $campaign->getBalance());
        $this->assertEquals(Campaign::STATUS_PAUSED, $campaign->getStatus());
    }

    /**
     * архивируем активную кампанию
     */
    public function testArchiveActiveCampaign()
    {
        /** @var User $advertiser */
        $advertiser = self::$parameters['fixtures']['advertisers'][2];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][7];

        $userBalanceBefore = $advertiser->getBalance();

        $url = self::$router->generate('api_put_campaign_status', array('id' => $campaign->getId()));

        $data = ['campaign' => ['status' => Campaign::STATUS_ARCHIVED]];
        $this->sendRequest($advertiser, 'PUT', $url, $data);

        $response = self::$client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        self::$em->refresh($campaign);
        self::$em->refresh($advertiser);

        // списались деньги с баланса кампании и зачислились юзеру
        $this->assertEquals(0, $campaign->getBalance());
        $this->assertEquals($userBalanceBefore + 10, $advertiser->getBalance());
    }
}
