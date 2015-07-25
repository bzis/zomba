<?php

namespace Vifeed\CampaignBundle\Tests\Controller;

use PhpAmqpLib\Message\AMQPMessage;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\UserBundle\Entity\User;

/**
 * Class CampaignControllerEditCampaignTest
 *
 * @package Vifeed\CampaignBundle\Tests\Controller
 */
class CampaignControllerEditCampaignTest extends CampaignControllerTestCase
{

    /**
     * попытка отредактировать кампанию без авторизации
     */
    public function testEditCampaignUnauthorized()
    {
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][1];
        $url = self::$router->generate('api_put_campaign', array('id' => $campaign->getId()));
        self::$client->request('PUT', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * попытка отредактировать несуществуюущую кампанию
     */
    public function testEditNonExistentCampaign()
    {
        $advertiser = self::$parameters['fixtures']['advertisers'][0];

        $url = self::$router->generate('api_put_campaign', array('id' => -1));
        $this->sendRequest($advertiser, 'PUT', $url);
        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());
    }

    /**
     * попытка отредактировать чужую кампанию
     */
    public function testEditNotOwnCampaign()
    {
        $advertiser = self::$parameters['fixtures']['advertisers'][0];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][1];

        $url = self::$router->generate('api_put_campaign', array('id' => $campaign->getId()));

        $this->sendRequest($advertiser, 'PUT', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * редактирование своей кампании
     *
     * @return int
     */
    public function testEditOwnCampaign()
    {
        $advertiser = self::$parameters['fixtures']['advertisers'][0];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][0];

        $url = self::$router->generate('api_put_campaign', array('id' => $campaign->getId()));

        $data = ['campaign' => ['name' => 'edited', 'description' => 'edited2']];
        $this->sendRequest($advertiser, 'PUT', $url, $data);
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        return $campaign->getId();
    }

    /**
     * данные отредактированной кампании изменились
     *
     * @param int $campaignId
     *
     * @depends testEditOwnCampaign
     */
    public function testEditedCampaignChanged($campaignId)
    {
        $advertiser = self::$parameters['fixtures']['advertisers'][0];

        $dateTime = new \DateTime();

        $url = self::$router->generate('api_get_campaign', array('id' => $campaignId));
        $this->sendRequest($advertiser, 'GET', $url);
        $data = json_decode(self::$client->getResponse()->getContent(), JSON_UNESCAPED_UNICODE);
        $this->assertEquals('edited', $data['name']);
        $this->assertEquals('edited2', $data['description']);

        $updatedAt = \DateTime::createFromFormat(\DateTime::ISO8601, $data['updated_at'])->getTimestamp();
        $this->assertLessThanOrEqual(3, time() - $updatedAt);
    }

    /**
     * у запущенной кампании можно редактировать только название и описание
     */
    public function testEditStartedCampaignBid()
    {
        $advertiser = self::$parameters['fixtures']['advertisers'][0];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][0];

        $url = self::$router->generate('api_put_campaign', array('id' => $campaign->getId()));

        $data = ['campaign' => ['bid' => 6]];
        $this->sendRequest($advertiser, 'PUT', $url, $data);
        $response = self::$client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());

        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->validateErrors($content, ['Эта форма не должна содержать дополнительных полей.']);
    }

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



}
