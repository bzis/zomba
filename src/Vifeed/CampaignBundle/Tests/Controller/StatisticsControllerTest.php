<?php

namespace Vifeed\CampaignBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\GeoBundle\Entity\City;
use Vifeed\GeoBundle\Entity\Country;
use Vifeed\PaymentBundle\Manager\VideoViewPaymentManager;
use Vifeed\PlatformBundle\Entity\Platform;
use Vifeed\VideoViewBundle\Entity\VideoView;
use Vifeed\UserBundle\Entity\User;
use Vifeed\VideoViewBundle\Manager\StatsManager;

class StatisticsControllerTest extends CampaignControllerTestCase
{
    /**
     * попытка доступа к отчетам для рекламодателя паблишером
     */
    public function testAccessAdvertiserReportsWithPublisher1()
    {
        $campaign = self::$parameters['fixtures']['campaigns'][0];
        $url = self::$router->generate('api_get_cities_stats', ['id' => $campaign->getId()]);
        $this->sendRequest(self::$testPublisher, 'GET', $url);
        $response = self::$client->getResponse();
        $this->assertEquals(403, $response->getStatusCode(), $response->getContent());
    }

    /**
     * попытка доступа к отчетам для рекламодателя паблишером
     */
    public function testAccessAdvertiserReportsWithPublisher2()
    {
        $campaign = self::$parameters['fixtures']['campaigns'][0];
        $url = self::$router->generate('api_get_countries_stats', ['id' => $campaign->getId()]);
        $this->sendRequest(self::$testPublisher, 'GET', $url);
        $response = self::$client->getResponse();
        $this->assertEquals(403, $response->getStatusCode(), $response->getContent());
    }

    /**
     * попытка доступа к отчетам для рекламодателя паблишером
     */
    public function testAccessAdvertiserReportsWithPublisher3()
    {
        $campaign = self::$parameters['fixtures']['campaigns'][0];
        $url = self::$router->generate('api_get_daily_stats', ['id' => $campaign->getId()]);
        $this->sendRequest(self::$testPublisher, 'GET', $url);
        $response = self::$client->getResponse();
        $this->assertEquals(403, $response->getStatusCode(), $response->getContent());
    }

    /**
     * попытка доступа к отчетам для рекламодателя паблишером
     */
    public function testAccessAdvertiserReportsWithPublisher4()
    {
        $campaign = self::$parameters['fixtures']['campaigns'][0];
        $url = self::$router->generate('api_get_hourly_stats', ['id' => $campaign->getId(), 'day' => 'today']);
        $this->sendRequest(self::$testPublisher, 'GET', $url);
        $response = self::$client->getResponse();
        $this->assertEquals(403, $response->getStatusCode(), $response->getContent());
    }

    /**
     * попытка доступа к отчетам для рекламодателя паблишером
     */
    public function testAccessAdvertiserReportsWithPublisher5()
    {
        $campaign = self::$parameters['fixtures']['campaigns'][0];
        $country = self::$parameters['fixtures']['countries'][0];

        $url = self::$router->generate('api_get_cities_stats_by_country',
                                       ['id' => $campaign->getId(), 'country_id' => $country->getId()]
        );
        $this->sendRequest(self::$testPublisher, 'GET', $url);
        $response = self::$client->getResponse();
        $this->assertEquals(403, $response->getStatusCode(), $response->getContent());
    }

    /**
     * попытка доступа к почасовым отчетам с неправильным указанием дня
     * (должно быть today или yesterday)
     */
    public function testGetHourlyStatsActionWrongDay()
    {
        $campaign = self::$parameters['fixtures']['campaigns'][0];
        $url = self::$router->generate('api_get_hourly_stats', ['id' => $campaign->getId(), 'day' => 'aaa']);
        $this->sendRequest(self::$testAdvertiser, 'GET', $url);
        $response = self::$client->getResponse();
        $this->assertEquals(404, $response->getStatusCode(), $response->getContent());
    }

    /**
     * статистика по городам
     */
    public function testGetCitiesStatsAction()
    {
        /** @var User $advertiser */
        $advertiser = self::$parameters['fixtures']['advertisers'][0];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][0];

        $url = self::$router->generate('api_get_cities_stats', ['id' => $campaign->getId()]);

        $data = ['date_from' => (new \DateTime('yesterday'))->format('Y-m-d'),
                 'date_to'   => (new \DateTime('today'))->format('Y-m-d')];

        $this->sendRequest($advertiser, 'GET', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->assertInternalType('array', $content);
        $this->assertCount(3, $content);

        $this->assertCount(5, $content[0]);
        $this->assertArrayHasKey('views', $content[0]);
        $this->assertArrayHasKey('name', $content[0]);
        $this->assertArrayHasKey('city_id', $content[0]);
        $this->assertArrayHasKey('latitude', $content[0]);
        $this->assertArrayHasKey('longitude', $content[0]);
        $this->assertEquals('Москва', $content[0]['name']);
        $this->assertEquals(2, $content[0]['views']);
        $this->assertEquals('111.111', $content[0]['latitude']);
        $this->assertEquals('111.222', $content[0]['longitude']);

        $this->assertEquals(null, $content[1]['name']);
        $this->assertEquals(1, $content[1]['views']);
        $this->assertEquals(null, $content[1]['latitude']);
        $this->assertEquals(null, $content[1]['longitude']);

        $this->assertEquals('Воронеж', $content[2]['name']);
        $this->assertEquals(1, $content[2]['views']);
        $this->assertEquals('222.222', $content[2]['latitude']);
        $this->assertEquals('222.333', $content[2]['longitude']);
    }

    /**
     * статистика по странам
     */
    public function testGetCountriesStatsAction()
    {
        /** @var User $advertiser */
        $advertiser = self::$parameters['fixtures']['advertisers'][0];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][0];

        $url = self::$router->generate('api_get_countries_stats', ['id' => $campaign->getId()]);

        $data = ['date_from' => (new \DateTime('yesterday'))->format('Y-m-d'),
                 'date_to'   => (new \DateTime('today'))->format('Y-m-d')];

        $this->sendRequest($advertiser, 'GET', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->assertInternalType('array', $content);
        $this->assertCount(2, $content);

        $this->assertCount(4, $content[0]);
        $this->assertArrayHasKey('views', $content[0]);
        $this->assertArrayHasKey('name', $content[0]);
        $this->assertArrayHasKey('country_id', $content[0]);
        $this->assertArrayHasKey('percentage', $content[0]);
        $this->assertEquals('Россия', $content[0]['name']);
        $this->assertEquals(3, $content[0]['views']);
        $this->assertEquals(75, $content[0]['percentage']);

        $this->assertEquals('Белоруссия', $content[1]['name']);
        $this->assertEquals(1, $content[1]['views']);
        $this->assertEquals(25, $content[1]['percentage']);
    }

    /**
     * статистика по городам в стране
     */
    public function testGetCitiesStatsByCountryAction()
    {
        /** @var User $advertiser */
        $advertiser = self::$parameters['fixtures']['advertisers'][0];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][0];
        /** @var Country $country */
        $country = self::$parameters['fixtures']['countries'][0];

        $url = self::$router->generate(
              'api_get_cities_stats_by_country',
              ['id' => $campaign->getId(), 'country_id' => $country->getId()]
        );

        $data = ['date_from' => (new \DateTime('yesterday'))->format('Y-m-d'),
                 'date_to'   => (new \DateTime('today'))->format('Y-m-d')];

        $this->sendRequest($advertiser, 'GET', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->assertInternalType('array', $content);
        $this->assertCount(2, $content);

        $this->assertCount(6, $content[0]);
        $this->assertArrayHasKey('views', $content[0]);
        $this->assertArrayHasKey('name', $content[0]);
        $this->assertArrayHasKey('city_id', $content[0]);
        $this->assertArrayHasKey('latitude', $content[0]);
        $this->assertArrayHasKey('longitude', $content[0]);
        $this->assertArrayHasKey('percentage', $content[0]);
        $this->assertEquals('Москва', $content[0]['name']);
        $this->assertEquals(2, $content[0]['views']);
        $this->assertEquals(67, $content[0]['percentage']);

        $this->assertEquals('Воронеж', $content[1]['name']);
        $this->assertEquals(1, $content[1]['views']);
        $this->assertEquals(33, $content[1]['percentage']);
    }

    /**
     * статистика по дням
     */
    public function testGetDailyStatsAction()
    {
        /** @var User $advertiser */
        $advertiser = self::$parameters['fixtures']['advertisers'][0];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][0];

        $url = self::$router->generate('api_get_daily_stats', ['id' => $campaign->getId()]);

        $data = ['date_from' => (new \DateTime('yesterday'))->format('Y-m-d'),
                 'date_to'   => (new \DateTime('today'))->format('Y-m-d')];

        $this->sendRequest($advertiser, 'GET', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->assertInternalType('array', $content);
        $this->assertCount(2, $content);
        $this->assertArrayHasKey('date', $content[0]);
        $this->assertArrayHasKey('views', $content[0]);
        $this->assertArrayHasKey('paid_views', $content[0]);

        $this->assertEquals((new \DateTime('yesterday'))->format('Y-m-d'), $content[0]['date']);
        $this->assertEquals(3, $content[0]['views']);
        $this->assertEquals(1, $content[0]['paid_views']);

        $this->assertEquals((new \DateTime('today'))->format('Y-m-d'), $content[1]['date']);
        $this->assertEquals(1, $content[1]['views']);
        $this->assertEquals(0, $content[1]['paid_views']);
    }

    /**
     * статистика по часам
     */
    public function testGetHourlyStatsActionToday()
    {
        /** @var User $advertiser */
        $advertiser = self::$parameters['fixtures']['advertisers'][0];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][0];

        $url = self::$router->generate('api_get_hourly_stats', ['id' => $campaign->getId(), 'day' => 'today']);

        $this->sendRequest($advertiser, 'GET', $url);

        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->assertInternalType('array', $content);
        $this->assertCount(1, $content);
        $this->assertCount(3, $content[0]);
        $this->assertArrayHasKey('views', $content[0]);
        $this->assertArrayHasKey('paid_views', $content[0]);
        $this->assertArrayHasKey('hour', $content[0]);
        $this->assertEquals(1, $content[0]['views']);
        $this->assertEquals(0, $content[0]['paid_views']);
        $this->assertEquals(23, $content[0]['hour']);
    }

    /**
     * статистика по часам
     */
    public function testGetHourlyStatsActionYesterday()
    {
        /** @var User $advertiser */
        $advertiser = self::$parameters['fixtures']['advertisers'][0];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][0];

        $url = self::$router->generate('api_get_hourly_stats', ['id' => $campaign->getId(), 'day' => 'yesterday']);

        $this->sendRequest($advertiser, 'GET', $url);

        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
        $this->assertInternalType('array', $content);
        $this->assertCount(2, $content);
        $this->assertEquals(2, $content[0]['views']);
        $this->assertEquals(1, $content[0]['paid_views']);
        $this->assertEquals('00', $content[0]['hour']);
        $this->assertEquals(1, $content[1]['views']);
        $this->assertEquals(0, $content[1]['paid_views']);
        $this->assertEquals(23, $content[1]['hour']);
    }

    protected static function loadTestFixtures()
    {
        $fixtures = parent::loadTestFixtures();
        /** @var EntityManager $entityManager */
        $entityManager = self::$em;
        /** @var VideoViewPaymentManager $viewPaymentManager */
        $viewPaymentManager = self::getContainer()->get('vifeed.payment.video_view_payment_manager');
        /** @var StatsManager $statsManager */
        $statsManager = self::getContainer()->get('vifeed.videoview.stats_manager');

        $platform = new Platform();
        $platform
              ->setName('111')
              ->setUser($fixtures['publisher'])
              ->setUrl('111')
              ->setDescription('');
        $entityManager->persist($platform);

        $city1 = new City();
        $city1->setName('Москва')
              ->setCountry($fixtures['countries'][0])
              ->setLatitude('111.111')
              ->setLongitude('111.222');
        $entityManager->persist($city1);

        $city2 = new City();
        $city2->setName('Воронеж')
              ->setCountry($fixtures['countries'][0])
              ->setLatitude('222.222')
              ->setLongitude('222.333');
        $entityManager->persist($city2);


        $view1 = new VideoView();
        $view1
              ->setCampaign($fixtures['campaigns'][0])
              ->setPlatform($platform)
              ->setCurrentTime(10)
              ->setTimestamp((new \DateTime('yesterday'))->setTime(0, 0, 0)->format('U'))
              ->setCountry($fixtures['countries'][0])
              ->setCity($city1)
              ->setTrackNumber(10)
              ->setViewerId(md5(1));
        $entityManager->persist($view1);
        $entityManager->flush($view1);
        $viewPaymentManager->reckon($view1);

        $view2 = new VideoView();
        $view2
              ->setCampaign($fixtures['campaigns'][0])
              ->setPlatform($platform)
              ->setCurrentTime(60)
              ->setTimestamp((new \DateTime('yesterday'))->setTime(0, 0, 0)->format('U'))
              ->setCountry($fixtures['countries'][0])
              ->setCity($city1)
              ->setTrackNumber(60)
              ->setViewerId(md5(2));
        $entityManager->persist($view2);
        $entityManager->flush($view2);
        $viewPaymentManager->reckon($view2);

        $view3 = new VideoView();
        $view3
              ->setCampaign($fixtures['campaigns'][0])
              ->setPlatform($platform)
              ->setCurrentTime(60)
              ->setTimestamp((new \DateTime('yesterday'))->setTime(23, 0, 0)->format('U'))
              ->setCountry($fixtures['countries'][0])
              ->setCity($city2)
              ->setTrackNumber(60)
              ->setViewerId(md5(3));
        $entityManager->persist($view3);
        $entityManager->flush($view3);
        $viewPaymentManager->reckon($view3);

        $view4 = new VideoView();
        $view4
              ->setCampaign($fixtures['campaigns'][0])
              ->setPlatform($platform)
              ->setCurrentTime(60)
              ->setTimestamp((new \DateTime('today'))->setTime(23, 29, 59)->format('U'))
              ->setCountry($fixtures['countries'][1])
              ->setTrackNumber(60)
              ->setViewerId(md5(4));
        $entityManager->persist($view4);
        $entityManager->flush($view4);
        $viewPaymentManager->reckon($view4);

        $view5 = new VideoView();
        $view5
              ->setCampaign($fixtures['campaigns'][0])
              ->setPlatform($platform)
              ->setCurrentTime(60)
              ->setTimestamp((new \DateTime('today'))->setTime(23, 59, 59)->format('U'))
              ->setCountry($fixtures['countries'][1])
              ->setTrackNumber(60)
              ->setViewerId(md5(4));
        $entityManager->persist($view5);
        $entityManager->flush($view5);
        $viewPaymentManager->reckon($view5);

        $entityManager->flush();

        $statsManager->recollectAllStats();
        $statsManager->collectDailyStats((new \DateTime())->setTime(0, 0, 0));

        $fixtures['cities'] = [$city1, $city2];
        $fixtures['platform'] = $platform;

        return $fixtures;
    }
}
 