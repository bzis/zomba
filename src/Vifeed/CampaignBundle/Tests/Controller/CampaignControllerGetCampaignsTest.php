<?php

namespace Vifeed\CampaignBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\PaymentBundle\Manager\VideoViewPaymentManager;
use Vifeed\PlatformBundle\Entity\Platform;
use Vifeed\VideoViewBundle\Entity\VideoView;

/**
 * Class CampaignControllerGetCampaignsTest
 *
 * @package Vifeed\CampaignBundle\Tests\Controller
 */
class CampaignControllerGetCampaignsTest extends CampaignControllerTestCase
{

    /**
     * попытка посмотреть список кампаний без авторизации
     */
    public function testGetCampaignsListUnauthorized()
    {
        $url = self::$router->generate('api_get_campaigns');
        self::$client->request('GET', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * кампании, которые видит рекламодатель
     */
    public function testGetCampaignsListAdvertiser()
    {
        $url = self::$router->generate('api_get_campaigns');

        $advertisers = self::$parameters['fixtures']['advertisers'];
        /** @var Campaign[] $campaigns */
        $campaigns = self::$parameters['fixtures']['campaigns'];

        $fixtures = [
              [$advertisers[0], [$campaigns[0]->getId()]],
              [
                    $advertisers[1],
                    [
                          $campaigns[1]->getId(),
                          $campaigns[2]->getId(),
                          $campaigns[3]->getId(),
                          $campaigns[4]->getId(),
                          $campaigns[5]->getId()
                    ]
              ]
        ];

        // дата-провайдер тут не получается, потому что из него нет доступа к фикстурам
        while (list($advertiser, $campaignIds) = array_pop($fixtures)) {
            $this->sendRequest($advertiser, 'GET', $url);
            $response = self::$client->getResponse();
            $content = $response->getContent();

            $this->assertEquals(200, $response->getStatusCode());
            $this->assertJson($content);

            $content = json_decode($content, JSON_UNESCAPED_UNICODE);
            $this->assertInternalType('array', $content);
            $this->assertCount(sizeof($campaignIds), $content);

            foreach ($content as $element) {
                $this->assertContains($element['id'], $campaignIds);
            }
        }
    }

    /**
     * рекламодатель видит статистику по своим кампаниям
     */
    public function testGetCampaignsListStats()
    {
        $url = self::$router->generate('api_get_campaigns');

        $advertiser = self::$parameters['fixtures']['advertisers'][1];

        $this->sendRequest($advertiser, 'GET', $url);
        $response = self::$client->getResponse();
        $content = $response->getContent();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($content);

        $content = json_decode($content, JSON_UNESCAPED_UNICODE);

        $this->assertInternalType('array', $content);
        $this->assertArrayHasKey('total_views', $content[0]);
        $this->assertArrayHasKey('paid_views', $content[0]);
        $this->assertEquals(2, $content[0]['total_views']);
        $this->assertEquals(0, $content[0]['paid_views']);

        $this->assertEquals(2, $content[1]['total_views']);
        $this->assertEquals(2, $content[1]['paid_views']);

        $this->assertEquals(0, $content[2]['total_views']);
        $this->assertEquals(0, $content[2]['paid_views']);
    }


    /**
     * кампании, которые не видит паблишер
     */
    public function testGetCampaignsListPublisher()
    {
        $url = self::$router->generate('api_get_campaigns');

        $this->sendRequest(self::$testPublisher, 'GET', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * Кампания по id без авторизации
     */
    public function testGetCampaignUnauthorized()
    {
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][0];
        $url = self::$router->generate('api_get_campaign', array('id' => $campaign->getId()));
        self::$client->request('GET', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * Кампания по несуществующему id
     */
    public function testGetNonExistentCampaign()
    {
        $url = self::$router->generate('api_get_campaign', array('id' => -1));

        $this->sendRequest(self::$testPublisher, 'GET', $url);
        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());
    }

    /**
     * Своя удалённая кампания
     */
    public function testGetSoftDeletedCampaign()
    {
        $advertiser = self::$parameters['fixtures']['advertisers'][1];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][6];

        $url = self::$router->generate('api_get_campaign', array('id' => $campaign->getId()));
        $this->sendRequest($advertiser, 'GET', $url);

        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());
    }

    /**
     * рекламодетель пробует запросить чужую кампанию по id
     */
    public function testGetNotOwnCampaignWithAdvertiser()
    {
        $advertiser = self::$parameters['fixtures']['advertisers'][1];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][0];

        $url = self::$router->generate('api_get_campaign', array('id' => $campaign->getId()));
        $this->sendRequest($advertiser, 'GET', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * рекламодатель запрашивает свою кампанию по id
     */
    public function testGetOwnCampaign()
    {
        $advertiser = self::$parameters['fixtures']['advertisers'][0];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][0];

        $url = self::$router->generate('api_get_campaign', array('id' => $campaign->getId()));
        $this->sendRequest($advertiser, 'GET', $url);
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        $content = self::$client->getResponse()->getContent();
        $this->assertJson($content);
        $data = json_decode($content, JSON_UNESCAPED_UNICODE);

        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals($campaign->getId(), $data['id']);
        $this->assertArrayHasKey('social_data', $data);
        $this->assertArrayHasKey('youtube_data', $data);
    }

    /**
     * паблишер запрашивает кампанию по id
     */
    public function testGetCampaignWithPublisher()
    {
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][0];

        $url = self::$router->generate('api_get_campaign', array('id' => $campaign->getId()));
        $this->sendRequest(self::$testPublisher, 'GET', $url);
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        $content = self::$client->getResponse()->getContent();
        $this->assertJson($content);
        $data = json_decode($content, JSON_UNESCAPED_UNICODE);

        $keys = ['id', 'hash_id', 'name', 'hash', 'description', 'gender', 'countries', 'tags', 'age_ranges', 'bid',
                 'general_budget', 'general_budget_remains'];
        $this->assertArrayHasOnlyKeys($keys, $data);

        $this->assertEquals($campaign->getId(), $data['id']);
        $this->assertEquals(7, $data['general_budget']);
        $this->assertEquals(7, $data['general_budget_remains']);
        $this->assertEquals(2.1, $data['bid']);
    }

    protected static function loadTestFixtures()
    {
        $fixtures = parent::loadTestFixtures();

        /** @var EntityManager $entityManager */
        $entityManager = self::$em;
        /** @var VideoViewPaymentManager $viewPaymentManager */
        $viewPaymentManager = self::getContainer()->get('vifeed.payment.video_view_payment_manager');

        $platform1 = new Platform();
        $platform1
              ->setName('111')
              ->setUser($fixtures['publisher'])
              ->setUrl('111')
              ->setDescription('');
        $entityManager->persist($platform1);

        $view1 = new VideoView();
        $view1
              ->setCampaign($fixtures['campaigns'][2])
              ->setPlatform($platform1)
              ->setCurrentTime(60)
              ->setTimestamp((new \DateTime('2014-03-22'))->format('U'))
              ->setTrackNumber(60)
              ->setViewerId(md5(1));
        $entityManager->persist($view1);
        $viewPaymentManager->reckon($view1);

        $view2 = new VideoView();
        $view2
              ->setCampaign($fixtures['campaigns'][2])
              ->setPlatform($platform1)
              ->setCurrentTime(60)
              ->setTimestamp((new \DateTime('2014-03-23'))->format('U'))
              ->setTrackNumber(60)
              ->setViewerId(md5(2));
        $entityManager->persist($view2);
        $viewPaymentManager->reckon($view2);

        $view3 = new VideoView();
        $view3
              ->setCampaign($fixtures['campaigns'][1])
              ->setPlatform($platform1)
              ->setCurrentTime(60)
              ->setTimestamp((new \DateTime('2014-03-23'))->format('U'))
              ->setTrackNumber(60)
              ->setViewerId(md5(3));
        $entityManager->persist($view3);
        $viewPaymentManager->reckon($view3);

        $view4 = new VideoView();
        $view4
              ->setCampaign($fixtures['campaigns'][1])
              ->setPlatform($platform1)
              ->setCurrentTime(10)
              ->setTimestamp((new \DateTime('2014-03-23'))->format('U'))
              ->setTrackNumber(10)
              ->setViewerId(md5(4));
        $entityManager->persist($view4);
        $viewPaymentManager->reckon($view4);

        self::$em->refresh($fixtures['campaigns'][1]);
        self::$em->refresh($fixtures['campaigns'][2]);

        return $fixtures;
    }

}
