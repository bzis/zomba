<?php

namespace Vifeed\CampaignBundle\Tests\Controller;

use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\UserBundle\Entity\User;

/**
 * Class CampaignControllerDeleteCampaignTest
 *
 * @package Vifeed\CampaignBundle\Tests\Controller
 */
class CampaignControllerDeleteCampaignTest extends CampaignControllerTestCase
{

    /**
     * попытка удалить кампанию без авторизации
     */
    public function testDeleteCampaignUnauthorized()
    {
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][0];
        $url = self::$router->generate('api_delete_campaign', array('id' => $campaign->getId()));

        self::$client->request('DELETE', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * попытка удалить несуществующую кампанию
     */
    public function testDeleteNonExistentCampaign()
    {
        /** @var User $advertiser */
        $advertiser = self::$parameters['fixtures']['advertisers'][0];

        $url = self::$router->generate('api_delete_campaign', array('id' => -1));

        $this->sendRequest($advertiser, 'DELETE', $url, []);
        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());
    }

    /**
     * попытка удалить чужую кампанию
     */
    public function testDeleteNotOwnCampaign()
    {
        /** @var User $advertiser */
        $advertiser = self::$parameters['fixtures']['advertisers'][1];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][0];

        $url = self::$router->generate('api_delete_campaign', array('id' => $campaign->getId()));
        $this->sendRequest($advertiser, 'DELETE', $url, array());
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * удалить свою кампанию
     * удаляем $cmapaigns[0] из фикстур
     *
     * @return int
     */
    public function testDeleteCampaignOk()
    {
        /** @var User $advertiser */
        $advertiser = self::$parameters['fixtures']['advertisers'][0];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][0];

        $url = self::$router->generate('api_delete_campaign', array('id' => $campaign->getId()));

        $this->sendRequest($advertiser, 'DELETE', $url, array());
        $this->assertEquals(204, self::$client->getResponse()->getStatusCode());

        return $campaign->getId();
    }

    /**
     * проверка, что удалённая кампания не находится
     *
     * @param int $campaignId
     *
     * @depends testDeleteCampaignOk
     */
    public function testGetDeletedCampaign($campaignId)
    {
        /** @var User $advertiser */
        $advertiser = self::$parameters['fixtures']['advertisers'][0];

        $url = self::$router->generate('api_get_campaign', array('id' => $campaignId));
        $this->sendRequest($advertiser, 'GET', $url);
        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());
    }

    /**
     * проверяем, что кампания осталась в базе с пометкой deletedAt
     *
     * @param int $campaignId
     *
     * @depends testDeleteCampaignOk
     */
    public function testDeletedCampaignRemainsInDB($campaignId)
    {
        $campaign = $this->getEntityManager()->getConnection()->fetchAll('SELECT * FROM campaign WHERE id = :id', ['id' => $campaignId]);
        $this->assertInternalType('array', $campaign);
        $this->assertCount(1, $campaign);
        $this->assertArrayHasKey('deleted_at', $campaign[0]);
        $this->assertNotNull($campaign[0]['deleted_at']);

        $deletedAt = date_parse($campaign[0]['deleted_at']);
        $this->assertInternalType('array', $deletedAt);
        $this->assertArrayHasKey('errors', $deletedAt);
        $this->assertEmpty($deletedAt['errors']);
    }
}
