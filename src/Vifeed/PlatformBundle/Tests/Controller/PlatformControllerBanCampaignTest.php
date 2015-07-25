<?php

namespace Vifeed\PlatformBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\PlatformBundle\Entity\Platform;
use Vifeed\UserBundle\Entity\User;

/**
 * Class CampaignControllerBanCampaignTest
 *
 * @package Vifeed\CampaignBundle\Tests\Controller
 */
class PlatformControllerBanCampaignTest extends PlatformControllerTestCase
{
    /**
     * список кампаний для площадки 1, одна забанена
     */
    public function testBannedCampaignsListPlatform1()
    {
        $publisher = self::$parameters['fixtures']['publisher'];
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platforms'][0];

        $url = self::$router->generate('api_get_platform_campaigns', ['id' => $platform->getId()]);
        $this->sendRequest($publisher, 'GET', $url);
        $response = self::$client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertJson($content);

        $content = json_decode($content, JSON_UNESCAPED_UNICODE);

        $this->assertInternalType('array', $content);
        $this->assertCount(3, $content);

        $this->assertArrayHasKey('banned', $content[0]);
        $this->assertEquals($content[0]['banned'], true);
        $this->assertEquals($content[1]['banned'], false);
        $this->assertEquals($content[2]['banned'], false);
    }

    /**
     * список кампаний для площадки 2, нет забаненных
     */
    public function testBannedCampaignsListPlatform2()
    {
        $publisher = self::$parameters['fixtures']['publisher'];
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platforms'][1];

        $url = self::$router->generate('api_get_platform_campaigns', ['id' => $platform->getId()]);
        $this->sendRequest($publisher, 'GET', $url);
        $response = self::$client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertJson($content);

        $content = json_decode($content, JSON_UNESCAPED_UNICODE);
        $this->assertInternalType('array', $content);

        $this->assertEquals($content[0]['banned'], false);
        $this->assertEquals($content[1]['banned'], false);
        $this->assertEquals($content[2]['banned'], false);
    }

    /**
     * попытка бана кампании неавторизованным пользователем
     */
    public function testCampaignBanUnauthorized()
    {
        $url = self::$router->generate('api_put_campaign_ban', ['id' => 0, 'campaign_id' => 0]);
        self::$client->request('PUT', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * попытка бана кампании рекламодателем
     */
    public function testCampaignBanAdvertiser()
    {
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platforms'][0];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][1];

        $url = self::$router->generate(
                            'api_put_campaign_ban',
                            ['id' => $platform->getId(), 'campaign_id' => $campaign->getId()]
        );
        $this->sendRequest(self::$testAdvertiser, 'PUT', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * попытка бана несуществующей кампании
     */
    public function testCampaignBanCampaignNotExist()
    {
        $publisher = self::$parameters['fixtures']['publisher'];
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platforms'][0];

        $url = self::$router->generate(
                            'api_put_campaign_ban', ['id' => $platform->getId(), 'campaign_id' => 0]
        );
        $this->sendRequest($publisher, 'PUT', $url);
        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());
    }

    /**
     * попытка бана несуществующей кампании
     */
    public function testCampaignBanPlatformNotExist()
    {
        $publisher = self::$parameters['fixtures']['publisher'];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][0];

        $url = self::$router->generate(
                            'api_put_campaign_ban', ['id' => 100500, 'campaign_id' => $campaign->getId()]
        );
        $this->sendRequest($publisher, 'PUT', $url);
        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());
    }

    /**
     * бан кампании паблишером
     * баним $campaign2
     *
     * @return int id забаненной кампании
     */
    public function testCampaignBanOk()
    {
        $publisher = self::$parameters['fixtures']['publisher'];
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platforms'][0];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][1];

        $url = self::$router->generate(
                            'api_put_campaign_ban',
                            ['id' => $platform->getId(), 'campaign_id' => $campaign->getId()]
        );
        $this->sendRequest($publisher, 'PUT', $url);
        $this->assertEquals(201, self::$client->getResponse()->getStatusCode());

        return $campaign->getId();
    }

    /**
     * проверяем, что плолщадка забанена
     *
     * @param int $campaign
     *
     * @depends testCampaignBanOk
     */
    public function testCampaignBanned($campaign)
    {
        $publisher = self::$parameters['fixtures']['publisher'];
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platforms'][0];

        $url = self::$router->generate('api_get_platform_campaigns', ['id' => $platform->getId()]);
        $this->sendRequest($publisher, 'GET', $url);
        $response = self::$client->getResponse();
        $content = $response->getContent();
        $content = json_decode($content, JSON_UNESCAPED_UNICODE);

        $this->assertEquals($campaign, $content[1]['id']);
        $this->assertEquals($content[1]['banned'], true);

    }

    /**
     * попытка повторного бана кампании паблишером
     */
    public function testCampaignBanRepeated()
    {
        $publisher = self::$parameters['fixtures']['publisher'];
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platforms'][0];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][0];

        $url = self::$router->generate(
                            'api_put_campaign_ban',
                            ['id' => $platform->getId(), 'campaign_id' => $campaign->getId()]
        );
        $this->sendRequest($publisher, 'PUT', $url);
        $this->assertEquals(409, self::$client->getResponse()->getStatusCode());
    }


    /**
     * попытка разбана кампании неавторизованным пользователем
     */
    public function testCampaignUnbanUnauthorized()
    {
        $url = self::$router->generate('api_delete_campaign_ban', ['id' => 0, 'campaign_id' => 0]);
        self::$client->request('DELETE', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * попытка разбана кампании рекламодателем
     */
    public function testCampaignUnbanAdvertiser()
    {
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platforms'][0];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][1];

        $url = self::$router->generate(
                            'api_delete_campaign_ban',
                            ['id' => $platform->getId(), 'campaign_id' => $campaign->getId()]
        );
        $this->sendRequest(self::$testAdvertiser, 'DELETE', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * попытка разбана несуществующей кампании
     */
    public function testCampaignUnbanCampaignNotExist()
    {
        $publisher = self::$parameters['fixtures']['publisher'];
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platforms'][0];

        $url = self::$router->generate(
                            'api_delete_campaign_ban', ['id' => $platform->getId(), 'campaign_id' => 0]
        );
        $this->sendRequest($publisher, 'DELETE', $url);
        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());
    }

    /**
     * попытка разбана несуществующей кампании
     */
    public function testCampaignUnbanPlatformNotExist()
    {
        $publisher = self::$parameters['fixtures']['publisher'];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][0];

        $url = self::$router->generate(
                            'api_delete_campaign_ban', ['id' => 100500, 'campaign_id' => $campaign->getId()]
        );
        $this->sendRequest($publisher, 'DELETE', $url);
        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());
    }

    /**
     * попытка разбана незабаненной кампании
     */
    public function testCampaignUnbanBanNotExist()
    {
        $publisher = self::$parameters['fixtures']['publisher'];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][2];
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platforms'][0];

        $url = self::$router->generate(
                            'api_delete_campaign_ban',
                            ['id' => $platform->getId(), 'campaign_id' => $campaign->getId()]
        );
        $this->sendRequest($publisher, 'DELETE', $url);
        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());
    }

    /**
     * разбан кампании паблишером
     * разбаним $campaigns[0]
     */
    public function testCampaignUnbanOk()
    {
        $publisher = self::$parameters['fixtures']['publisher'];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][0];
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platforms'][0];

        $url = self::$router->generate(
                            'api_delete_campaign_ban',
                            ['id' => $platform->getId(), 'campaign_id' => $campaign->getId()]
        );
        $this->sendRequest($publisher, 'DELETE', $url);
        $this->assertEquals(204, self::$client->getResponse()->getStatusCode());

        return $campaign->getId();
    }

    /**
     * проверяем, что плолщадка забанена
     *
     * @param int $campaign
     *
     * @depends testCampaignUnbanOk
     */
    public function testCampaignUnbanned($campaign)
    {
        $publisher = self::$parameters['fixtures']['publisher'];
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platforms'][0];

        $url = self::$router->generate('api_get_platform_campaigns', ['id' => $platform->getId()]);
        $this->sendRequest($publisher, 'GET', $url);
        $response = self::$client->getResponse();
        $content = $response->getContent();
        $content = json_decode($content, JSON_UNESCAPED_UNICODE);

        $this->assertEquals($campaign, $content[0]['id']);
        $this->assertEquals($content[0]['banned'], false);
    }


    /**
     * @return array
     */
    protected static function loadTestFixtures()
    {
        $userManager = self::getContainer()->get('fos_user.user_manager');
        /** @var EntityManager $entityManager */
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $campaignManager = self::getContainer()->get('vifeed.campaign.manager');
        $tokenManager = static::getContainer()->get('vifeed.user.wsse_token_manager');
        $platformManager = self::getContainer()->get('vifeed.platform.manager');


        /** @var User $advertiser */
        $advertiser = $userManager->createUser();
        $advertiser
              ->setEmail('testadvertiser1@vifeed.ru')
              ->setUsername('testadvertiser1@vifeed.ru')
              ->setBalance(100)
              ->setEnabled(true)
              ->setType(User::TYPE_ADVERTISER)
              ->setPlainPassword('12345');

        $userManager->updateUser($advertiser, false);

        /** @var User $publisher */
        $publisher = $userManager->createUser();
        $publisher
              ->setEmail('testpublisher1@vifeed.ru')
              ->setUsername('testpublisher1@vifeed.ru')
              ->setBalance(100)
              ->setEnabled(true)
              ->setType(User::TYPE_PUBLISHER)
              ->setPlainPassword('12345');

        $userManager->updateUser($publisher, false);

        $platform1 = new Platform();
        $platform1->setUser($publisher)
                  ->setName('name1')
                  ->setDescription('111')
                  ->setUrl('url1');
        $entityManager->persist($platform1);

        $platform2 = new Platform();
        $platform2->setUser($publisher)
                  ->setName('name2')
                  ->setDescription('222')
                  ->setUrl('ya.ru');
        $entityManager->persist($platform2);

        $campaign1 = new Campaign();
        $campaign1
              ->setStatus(Campaign::STATUS_ON)
              ->setBid(3)
              ->setName('111')
              ->setUser($advertiser)
              ->setHash(substr(md5(mt_rand(1, 100)), 0, 11))
              ->setDailyBudget(7)
              ->setGeneralBudget(10);
        $campaignManager->save($campaign1);

        $campaign2 = new Campaign();
        $campaign2
              ->setStatus(Campaign::STATUS_ON)
              ->setBid(3)
              ->setName('222')
              ->setUser($advertiser)
              ->setHash(substr(md5(mt_rand(1, 100)), 0, 11))
              ->setDailyBudget(0)
              ->setGeneralBudget(10);
        $campaignManager->save($campaign2);

        $campaign3 = new Campaign();
        $campaign3
              ->setStatus(Campaign::STATUS_ON)
              ->setBid(3)
              ->setName('333')
              ->setHash(substr(md5(mt_rand(1, 100)), 0, 11))
              ->setUser($advertiser)
              ->setDailyBudget(10)
              ->setGeneralBudget(0);
        $campaignManager->save($campaign3);

        $platformManager->banCampaign($platform1, $campaign1);

        $entityManager->flush();

        $tokenManager->createUserToken($publisher->getId());

        return array(
              'advertiser' => $advertiser,
              'publisher'  => $publisher,
              'campaigns'  => [$campaign1, $campaign2, $campaign3],
              'platforms'  => [$platform1, $platform2],
        );
    }

}
