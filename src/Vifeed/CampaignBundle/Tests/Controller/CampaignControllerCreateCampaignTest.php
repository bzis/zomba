<?php

namespace Vifeed\CampaignBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Hashids\Hashids;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\GeoBundle\Entity\Country;

/**
 * Class CampaignControllerCreateCampaignTest
 *
 * @package Vifeed\CampaignBundle\Tests\Controller
 */
class CampaignControllerCreateCampaignTest extends CampaignControllerTestCase
{

    /**
     * попытка создания кампании без авторизации
     */
    public function testCreateCampaignUnauthorized()
    {
        $url = self::$router->generate('api_put_campaigns');
        self::$client->request('PUT', $url, []);

        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * ошибки при создании кампании
     *
     * @param array $data
     * @param int   $code
     * @param array $errors
     *
     * @dataProvider createCampaignErrorProvider
     */
    public function testCreateCampaignErros($data, $code, $errors)
    {
        $advertiser = self::$parameters['fixtures']['advertisers'][0];

        $url = self::$router->generate('api_put_campaigns');

        $this->sendRequest($advertiser, 'PUT', $url, $data);
        $response = self::$client->getResponse();

        $this->assertEquals($code, $response->getStatusCode(), $response->getContent());

        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->validateErrors($content, $errors);
    }

    /**
     * успешное создание кампании
     *
     * @return int
     */
    public function testCreateCampaignOk()
    {
        $url = self::$router->generate('api_put_campaigns');
        /** @var Country[] $countries */
        $countries = self::$parameters['fixtures']['countries'];

        $data = [
              'campaign' => [
                    'name'          => 'test1',
                    'hash'          => 'dafkjhasdfs',
                    'gender'        => 'male',
                    'bid'           => 5.2,
                    'generalBudget' => 100,
                    'dailyBudget'   => 0,
                    'countries'     => [$countries[0]->getId(), $countries[1]->getId()],
                    'ageRanges'     => [2, 4] // загружаются в глобальных фикстурах
              ]
        ];

        $this->sendRequest(self::$testAdvertiser, 'PUT', $url, $data);
        $response = self::$client->getResponse();

        $this->assertEquals(201, $response->getStatusCode(), $response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
        $this->assertInternalType('array', $content);
        $keys = ['id', 'hash_id', 'name', 'hash', 'description', 'gender', 'general_budget', 'daily_budget', 'general_budget_used',
                 'daily_budget_used', 'total_views', 'paid_views', 'bid', 'status', 'social_data', 'youtube_data', 'countries',
                 'tags', 'age_ranges', 'created_at', 'updated_at', 'balance', 'start_at'];
        $this->assertArrayHasOnlyKeys($keys, $content);
        $this->assertArrayHasOnlyKeys(['fbLikes', 'fbComments', 'fbShares', 'vkLikes', 'vkShares', 'gplusShares', 'linkedPlatforms',
                                       'updatedAt'], $content['social_data']);
        $this->assertInternalType('integer', $content['id']);
        $this->assertEquals(5.2, $content['bid']);
        $this->assertEquals(Campaign::STATUS_PAUSED, $content['status']);
        $this->assertInternalType('array', $content['countries']);
        $this->assertCount(2, $content['countries']);
        $this->assertEquals('Россия', $content['countries'][0]['name']);
        $this->assertEquals('Белоруссия', $content['countries'][1]['name']);
        $this->assertInternalType('array', $content['age_ranges']);
        $this->assertCount(2, $content['age_ranges']);
        $this->assertEquals('14-17', $content['age_ranges'][0]['name']);
        $this->assertEquals('25-34', $content['age_ranges'][1]['name']);

        $createdAt = \DateTime::createFromFormat(\DateTime::ISO8601, $content['created_at'])->getTimestamp();
        $updatedAt = \DateTime::createFromFormat(\DateTime::ISO8601, $content['updated_at'])->getTimestamp();
        $this->assertLessThanOrEqual(2, time() - $createdAt);
        $this->assertLessThanOrEqual(1, $updatedAt - $createdAt);

        return $content['id'];
    }

    /**
     * успешное создание кампании, статы с ютуба
     */
    public function testCreateCampaignOkStatistics()
    {
        $url = self::$router->generate('api_put_campaigns');

        $data = [
              'campaign' => [
                    'name'          => 'test5',
                    'hash'          => 'dafkjhsdfss',
                    'bid'           => 5.2,
                    'generalBudget' => 100,
                    'dailyBudget'   => 0,
                    'statistics'    => [
                          'duration' => 555
                    ]
              ]
        ];

        $this->sendRequest(self::$testAdvertiser, 'PUT', $url, $data);
        $response = self::$client->getResponse();

        $this->assertEquals(201, $response->getStatusCode(), $response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
        $campaign = $this->getEntityManager()->find('VifeedCampaignBundle:Campaign', $content['id']);
        $this->assertEquals(555, $campaign->getYoutubeData('duration'));

        $this->assertInternalType('array', $content);
    }

    /**
     * кампании, которые видит рекламодатель
     *
     * @depends testCreateCampaignOk
     */
    public function testGetCampaignsListAdvertiser($id)
    {
        $url = self::$router->generate('api_get_campaigns');

        $this->sendRequest(self::$testAdvertiser, 'GET', $url);
        $response = self::$client->getResponse();
        $content = $response->getContent();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($content);

        $content = json_decode($content, JSON_UNESCAPED_UNICODE);
        $this->assertInternalType('array', $content);
        $ids = [];
        foreach ($content as $element) {
            $ids[] = $element['id'];
        }
        $this->assertContains($id, $ids);
    }

    /**
     * верно выставлен hash_id
     *
     * @param int $campaignId
     *
     * @depends testCreateCampaignOk
     */
    public function testCreatedCampaignHashId($campaignId)
    {
        /** @var EntityManager $entityManager */
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        /** @var Hashids $hashIds */
        $hashIds = self::getContainer()->get('hashids');

        $campaign = $entityManager->find('VifeedCampaignBundle:Campaign', $campaignId);
        $this->assertInstanceOf('Vifeed\CampaignBundle\Entity\Campaign', $campaign);

        $hashId = $hashIds->decode($campaign->getHashId());

        $this->assertInternalType('array', $hashId);
        $this->assertCount(1, $hashId);
        $this->assertEquals($campaignId, $hashId[0]);
    }

    /**
     * создание кампании с такими же данными, как уже удалённая
     */
    public function testCreateCampaignLikeDeletedOne()
    {
        $url = self::$router->generate('api_put_campaigns');

        $advertiser = self::$parameters['fixtures']['advertisers'][1];

        $data = [
              'campaign' => [
                    'name'          => '777',
                    'hash'          => '0123456789a',
                    'bid'           => 3,
                    'generalBudget' => 100,
                    'dailyBudget'   => 10,
              ]
        ];

        $this->sendRequest($advertiser, 'PUT', $url, $data);
        $response = self::$client->getResponse();

        $content = $response->getContent();
        $this->assertEquals(201, $response->getStatusCode(), $content);
    }


    /**
     * data-provider для testCreateCampaignErros
     *
     * @return array
     */
    public function createCampaignErrorProvider()
    {
        return [
              [
                    [],
                    400,
                    ['name' => 'Название не должно быть пустым']
              ],
              [
                    [
                          'campaign' => [
                                'name'   => 'test1',
                                'gender' => 'aa',
                                'bid'    => 10,
                          ]
                    ],
                    400,
                    ['gender' => 'Выберите пол']
              ],
              [
                    [
                          'campaign' => [
                                'name'   => 'test1',
                                'gender' => 'male',
                                'status' => 10,
                          ]
                    ],
                    400,
                    ['Эта форма не должна содержать дополнительных полей.']
              ],
              [
                    [
                          'campaign' => [
                                'name'          => '111',
                                'hash'          => 'dafkjhasdfsdafasdf',
                                'gender'        => 'female',
                                'bid'           => 10,
                                'generalBudget' => 100,
                                'dailyBudget'   => 0,
                          ]
                    ],
                    400,
                    ['name' => 'У вас уже есть кампания с таким названием']
              ],
              [
                    [
                          'campaign' => [
                                'name'          => 'aaa',
                                'hash'          => 'dhfkjhasdfsdafasdf',
                                'gender'        => 'female',
                                'bid'           => 10,
                                'generalBudget' => 100,
                                'dailyBudget'   => 5,
                          ]
                    ],
                    400,
                    ['dailyBudget' => 'Дневной бюджет не должен быть меньше 10% от бюджета кампании']
              ],
              [
                    [
                          'campaign' => [
                                'name'          => 'aaa',
                                'hash'          => 'dhfkjhasdfsdafasdf',
                                'gender'        => 'female',
                                'bid'           => 0.5,
                                'generalBudget' => 50,
                                'dailyBudget'   => 5,
                          ]
                    ],
                    400,
                    [
                          'generalBudget' => 'Бюджет должен быть не меньше 100 руб',
                          'bid'           => 'Минимальная ставка - 1 руб',
                    ]
              ],
        ];

    }

}
