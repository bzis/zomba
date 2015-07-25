<?php

namespace Vifeed\PlatformBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\GeoBundle\Entity\Country;
use Vifeed\PlatformBundle\Entity\Platform;
use Vifeed\UserBundle\Entity\User;

/**
 * Class PlatformControllerGetPlatformCampaignsTest
 *
 * @package Vifeed\CampaignBundle\Tests\Controller
 */
class PlatformControllerGetPlatformCampaignsTest extends PlatformControllerTestCase
{
    /**
     * рекламодатель не может смотреть список кампаний по площадкам
     */
    public function testCampaignsListAdveriser()
    {
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platforms'][0];

        $url = self::$router->generate('api_get_platform_campaigns', ['id' => $platform->getId()]);
        $this->sendRequest(self::$testAdvertiser, 'GET', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * несуществующая площадка
     */
    public function testCampaignsListPlatformNotExist()
    {
        $url = self::$router->generate('api_get_platform_campaigns', ['id' => 0]);
        $this->sendRequest(self::$testPublisher, 'GET', $url);
        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());
    }

    /**
     * чужая площадка
     */
    public function testCampaignsListNotOwnPlatform()
    {
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platforms'][0];

        $url = self::$router->generate('api_get_platform_campaigns', ['id' => $platform->getId()]);
        $this->sendRequest(self::$testPublisher, 'GET', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * список кампаний для площадки
     */
    public function testCampaignsListAll()
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

        $keys = ['id', 'hash_id', 'name', 'hash', 'description', 'gender', 'countries', 'tags', 'age_ranges', 'banned', 'bid',
                 'general_budget', 'general_budget_remains'];
        $this->assertArrayHasOnlyKeys($keys, $content[0]);
        $this->assertEquals(2.1, $content[0]['bid']);
        $this->assertEquals(70, $content[0]['general_budget']);
        $this->assertEquals(70, $content[0]['general_budget_remains']);

        $headers = $response->headers;
        $this->assertTrue($headers->has('link'));
        $this->assertEquals(
             '</api/platforms/' . $platform->getId() .
             '/campaigns?per_page=10&page=2>; rel="next", </api/platforms/' . $platform->getId() .
             '/campaigns?per_page=10&page=2>; rel="last"',
             $headers->get('link')
        );
    }

    /**
     * список кампаний для площадки, вторая страница
     */
    public function testCampaignsListPage2()
    {
        $publisher = self::$parameters['fixtures']['publisher'];
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platforms'][0];

        $url = self::$router->generate('api_get_platform_campaigns', ['id' => $platform->getId()]);
        $this->sendRequest($publisher, 'GET', $url, ['page' => 2]);
        $response = self::$client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertJson($content);

        $content = json_decode($content, JSON_UNESCAPED_UNICODE);

        $this->assertCount(5, $content);

        $headers = $response->headers;
        $this->assertTrue($headers->has('link'));
        $this->assertEquals(
             '</api/platforms/' . $platform->getId() .
             '/campaigns?per_page=10&page=1>; rel="first", </api/platforms/' . $platform->getId() .
             '/campaigns?per_page=10&page=1>; rel="prev"',
             $headers->get('link')
        );
    }

    /**
     * список кампаний для площадки с измененным количеством кампаний на страницу
     */
    public function testCampaignsListPerPage()
    {
        $publisher = self::$parameters['fixtures']['publisher'];
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platforms'][0];

        $url = self::$router->generate('api_get_platform_campaigns', ['id' => $platform->getId()]);
        $this->sendRequest($publisher, 'GET', $url, ['per_page' => 3]);
        $response = self::$client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertJson($content);

        $content = json_decode($content, JSON_UNESCAPED_UNICODE);

        $this->assertCount(3, $content);

        $headers = $response->headers;
        $this->assertTrue($headers->has('link'));
        $this->assertEquals(
             '</api/platforms/' . $platform->getId() .
             '/campaigns?per_page=3&page=2>; rel="next", </api/platforms/' . $platform->getId() .
             '/campaigns?per_page=3&page=5>; rel="last"',
             $headers->get('link')
        );
    }

    /**
     * список кампаний для площадки с фильтром по стране
     */
    public function testCampaignsListCountryFilter()
    {
        $publisher = self::$parameters['fixtures']['publisher'];
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platforms'][0];
        /** @var Country $country */
        $country = self::$parameters['fixtures']['countries'][0];
        $campaigns = self::$parameters['fixtures']['campaigns'];

        $url = self::$router->generate('api_get_platform_campaigns', ['id' => $platform->getId()]);
        $this->sendRequest($publisher, 'GET', $url, ['countries' => [$country->getId()]]);
        $response = self::$client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertJson($content);

        $content = json_decode($content, JSON_UNESCAPED_UNICODE);

        $this->assertCount(5, $content); // 1 - с заданной страной и 4 без страны
        $ids = [];
        foreach ($content as $campaign) {
            $ids[] = $campaign['id'];
        }
        $this->assertContains($campaigns[0]->getId(), $ids);

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

        $platform = new Platform();
        $platform->setUser($publisher)
                 ->setName('name1')
                 ->setDescription('111')
                 ->setUrl('url1');
        $entityManager->persist($platform);

        $country1 = new Country();
        $country1->setName('Россия');
        $entityManager->persist($country1);

        $country2 = new Country();
        $country2->setName('США');
        $entityManager->persist($country2);

        $campaign1 = new Campaign();
        $campaign1
              ->setStatus(Campaign::STATUS_ON)
              ->setBid(3)
              ->setName('111')
              ->setHash(substr(md5(mt_rand(1, 100)), 0, 11))
              ->setUser($advertiser)
              ->setDailyBudget(10)
              ->setGeneralBudget(100)
              ->setBalance(100)
              ->addCountry($country1);
        $campaignManager->save($campaign1);

        // удалённая кампания
        $campaign2 = new Campaign();
        $campaign2
              ->setStatus(Campaign::STATUS_ON)
              ->setBid(3)
              ->setName('222')
              ->setHash(substr(md5(mt_rand(1, 100)), 0, 11))
              ->setUser($advertiser)
              ->setDailyBudget(10)
              ->setGeneralBudget(100)
              ->setBalance(100)
              ->setDeletedAt(new \DateTime('2014-02-10'));
        $campaignManager->save($campaign2);

        $campaigns = [$campaign1, $campaign2];
        for ($i = 0; $i < 14; $i++) {
            $campaign = new Campaign();
            $campaign
                  ->setStatus(Campaign::STATUS_ON)
                  ->setBid(3)
                  ->setName(md5($i))
                  ->setHash(substr(md5(mt_rand(1, 100)), 0, 11))
                  ->setUser($advertiser)
                  ->setDailyBudget(10)
                  ->setBalance(100)
                  ->setGeneralBudget(100);

            if ($i < 10) {
                $campaign->addCountry($country2);
            }
            $campaignManager->save($campaign);
            $campaigns[] = $campaign;
        }

        $entityManager->flush();

        $tokenManager->createUserToken($publisher->getId());

        return array(
              'advertiser' => $advertiser,
              'publisher'  => $publisher,
              'campaigns'  => $campaigns,
              'platforms'  => [$platform],
              'countries'  => [$country1, $country2]
        );
    }

}
