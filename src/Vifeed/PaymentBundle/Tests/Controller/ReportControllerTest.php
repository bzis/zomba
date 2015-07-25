<?php


namespace Vifeed\PaymentBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Model\UserManager;
use JMS\Payment\CoreBundle\Entity\PaymentInstruction;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\PaymentBundle\Entity\Order;
use Vifeed\PaymentBundle\Entity\Wallet;
use Vifeed\PaymentBundle\Entity\Withdrawal;
use Vifeed\PaymentBundle\Manager\VideoViewPaymentManager;
use Vifeed\PlatformBundle\Entity\Platform;
use Vifeed\VideoViewBundle\Entity\VideoView;
use Vifeed\SystemBundle\Tests\ApiTestCase;
use Vifeed\UserBundle\Entity\User;
use Vifeed\VideoViewBundle\Manager\StatsManager;

/**
 * Class ReportControllerTest
 *
 * @package Vifeed\PaymentBundle\Tests\Controller
 */
class ReportControllerTest extends ApiTestCase
{

    /**
     * попытка доступа к отчетам для рекламодателя паблишером
     */
    public function testAccessAdvertiserReportsWithPublisher()
    {
        $url = self::$router->generate('api_get_billing_spendings');
        $this->sendRequest(self::$testPublisher, 'GET', $url);
        $response = self::$client->getResponse();
        $this->assertEquals(403, $response->getStatusCode(), $response->getContent());
    }

    /**
     * попытка доступа к отчетам для рекламодателя паблишером
     */
    public function testAccessPaymentsReportsWithPublisher()
    {
        $url = self::$router->generate('api_get_billing_payments');
        $this->sendRequest(self::$testPublisher, 'GET', $url);
        $response = self::$client->getResponse();
        $this->assertEquals(403, $response->getStatusCode(), $response->getContent());
    }

    /**
     * попытка доступа к отчетам для паблишера рекламодателем
     */
    public function testAccessPublisherReportsWithAdvertiser()
    {
        $url = self::$router->generate('api_get_billing_earnings');
        $this->sendRequest(self::$testAdvertiser, 'GET', $url);
        $response = self::$client->getResponse();
        $this->assertEquals(403, $response->getStatusCode(), $response->getContent());
    }

    /**
     * попытка доступа к отчетам для паблишера рекламодателем
     */
    public function testAccessWithdrawalsReportsWithAdvertiser()
    {
        $url = self::$router->generate('api_get_billing_withdrawals');
        $this->sendRequest(self::$testAdvertiser, 'GET', $url);
        $response = self::$client->getResponse();
        $this->assertEquals(403, $response->getStatusCode(), $response->getContent());
    }

    /**
     * ошибки в датах
     *
     * @param array $data
     * @param array $errors
     *
     * @dataProvider getBillingSpendingsErrorsProvider
     */
    public function testGetBillingSpendingsErrors($data, $errors)
    {
        $url = self::$router->generate('api_get_billing_spendings');

        $this->sendRequest(self::$testAdvertiser, 'GET', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(400, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->validateErrors($content, $errors);
    }

    /**
     * отчет по тратам за часть периода
     */
    public function testGetBillingSpendingsOkShortPeriod()
    {
        /** @var User $advertiser */
        $advertiser = self::$parameters['fixtures']['advertiser'];

        $url = self::$router->generate('api_get_billing_spendings');

        $data = ['date_from' => '2014-02-22',
                 'date_to'   => '2014-02-22'];

        $this->sendRequest($advertiser, 'GET', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->assertInternalType('array', $content);
        $this->assertArrayHasOnlyKeys(['campaigns', 'total_charged', 'total_views', 'total_paid_views', 'total_kpi'], $content);
        $this->assertCount(1, $content['campaigns']);
        $this->assertArrayHasOnlyKeys(['id', 'hash_id', 'name', 'views', 'charged', 'paid_views', 'kpi'], $content['campaigns'][0]);
        $this->assertEquals('111', $content['campaigns'][0]['name']);
        $this->assertEquals('1', $content['campaigns'][0]['views']);
        $this->assertEquals('1', $content['campaigns'][0]['paid_views']);
        $this->assertEquals('1', $content['campaigns'][0]['kpi']);
        $this->assertEquals('3.00', $content['campaigns'][0]['charged']);
        $this->assertEquals(3, $content['total_charged']);
        $this->assertEquals(1, $content['total_views']);
        $this->assertEquals(1, $content['total_paid_views']);
        $this->assertEquals(1, $content['total_kpi']);
    }

    /**
     * отчет по тратам за весь период
     */
    public function testGetBillingSpendingsOkLongerPeriod()
    {
        /** @var User $advertiser */
        $advertiser = self::$parameters['fixtures']['advertiser'];

        $url = self::$router->generate('api_get_billing_spendings');

        $data = ['date_from' => '2014-02-22',
                 'date_to'   => '2014-02-23'];

        $this->sendRequest($advertiser, 'GET', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
        $this->assertInternalType('array', $content);
        $this->assertArrayHasOnlyKeys(['campaigns', 'total_charged', 'total_views', 'total_paid_views', 'total_kpi'], $content);

        $this->assertCount(2, $content['campaigns']);
        $this->assertEquals('111', $content['campaigns'][0]['name']);
        $this->assertEquals(2, $content['campaigns'][0]['views']);
        $this->assertEquals(1, $content['campaigns'][0]['paid_views']);
        $this->assertEquals(3, $content['campaigns'][0]['charged']);
        $this->assertEquals(2, $content['campaigns'][0]['kpi']);
        $this->assertEquals('222', $content['campaigns'][1]['name']);
        $this->assertEquals(2, $content['campaigns'][1]['views']);
        $this->assertEquals(2, $content['campaigns'][1]['paid_views']);
        $this->assertEquals(6, $content['campaigns'][1]['charged']);
        $this->assertEquals(1, $content['campaigns'][1]['kpi']);
        $this->assertEquals(9, $content['total_charged']);
        $this->assertEquals(4, $content['total_views']);
        $this->assertEquals(3, $content['total_paid_views']);
        $this->assertEquals(1.33, $content['total_kpi']);
    }

    /**
     * отчет по тратам по кампании
     */
    public function testGetBillingSpendingsCampaignOk()
    {
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][0];
        /** @var User $advertiser */
        $advertiser = self::$parameters['fixtures']['advertiser'];

        $url = self::$router->generate('api_get_billing_spendings_by_campaign', ['campaign_id' => $campaign->getId()]);

        $data = ['date_from' => '2013-12-10',
                 'date_to'   => '2014-02-23'];

        $this->sendRequest($advertiser, 'GET', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->assertInternalType('array', $content);
        $this->assertArrayHasOnlyKeys(['stats', 'total_views', 'total_paid_views', 'total_charged', 'total_kpi'], $content);
        $this->assertCount(2, $content['stats']);
        $this->assertArrayHasOnlyKeys(['views', 'charged', 'date', 'kpi', 'paid_views'], $content['stats'][0]);

        $this->assertEquals('2014-02-22', $content['stats'][0]['date']);
        $this->assertEquals(1, $content['stats'][0]['views']);
        $this->assertEquals(1, $content['stats'][0]['paid_views']);
        $this->assertEquals(1, $content['stats'][0]['kpi']);
        $this->assertEquals(3, $content['stats'][0]['charged']);
        $this->assertEquals('2014-02-23', $content['stats'][1]['date']);
        $this->assertEquals(1, $content['stats'][1]['views']);
        $this->assertEquals(0, $content['stats'][1]['paid_views']);
        $this->assertEquals(0, $content['stats'][1]['kpi']);
        $this->assertEquals(0, $content['stats'][1]['charged']);

        $this->assertEquals(3, $content['total_charged']);
        $this->assertEquals(2, $content['total_views']);
        $this->assertEquals(1, $content['total_paid_views']);
        $this->assertEquals(2, $content['total_kpi']);
    }

    /**
     * отчет по тратам по кампании
     */
    public function testGetBillingSpendingsDeletedCampaignOk()
    {
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][1];
        /** @var User $advertiser */
        $advertiser = self::$parameters['fixtures']['advertiser'];

        $url = self::$router->generate('api_get_billing_spendings_by_campaign', ['campaign_id' => $campaign->getId()]);

        $data = ['date_from' => '2013-12-10',
                 'date_to'   => '2014-02-23'];

        $this->sendRequest($advertiser, 'GET', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
        
        $this->assertInternalType('array', $content);
        $this->assertArrayHasOnlyKeys(['stats', 'total_views', 'total_paid_views', 'total_charged', 'total_kpi'], $content);
        $this->assertEquals(6, $content['total_charged']);
    }

    /**
     * отчет по платежам
     */
    public function testGetBillingPaymentsOk()
    {
        $advertiser = self::$parameters['fixtures']['advertiser'];
        $url = self::$router->generate('api_get_billing_payments');

        $data = ['date_from' => '2013-12-10',
                 'date_to'   => (new \DateTime())->format('Y-m-d')];

        $this->sendRequest($advertiser, 'GET', $url, $data);
        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->assertInternalType('array', $content);
        $this->assertArrayHasOnlyKeys(['payments', 'total'], $content);
        $this->assertCount(2, $content['payments']);
        $this->assertArrayHasOnlyKeys(['paymentSystemName', 'date', 'amount'], $content['payments'][0]);

        $this->assertEquals('robokassa', $content['payments'][0]['paymentSystemName']);
        $this->assertEquals(100, $content['payments'][0]['amount']);
        $this->assertEquals('robokassa', $content['payments'][1]['paymentSystemName']);
        $this->assertEquals(50, $content['payments'][1]['amount']);
        $this->assertEquals(150, $content['total']);
    }

    /**
     * отчет по доходам за часть периода
     */
    public function testGetBillingEarningsOkShortPeriod()
    {
        /** @var User $publisher */
        $publisher = self::$parameters['fixtures']['publisher'];

        $url = self::$router->generate('api_get_billing_earnings');

        $data = ['date_from' => '2014-02-23',
                 'date_to'   => '2014-02-23'];

        $this->sendRequest($publisher, 'GET', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->assertInternalType('array', $content);
        $this->assertArrayHasOnlyKeys(['platforms', 'total'], $content);

        $this->assertCount(1, $content['platforms']);
        $this->assertArrayHasOnlyKeys(['id', 'name', 'views', 'earned'], $content['platforms'][0]);

        $this->assertEquals('111', $content['platforms'][0]['name']);
        $this->assertEquals(2, $content['platforms'][0]['views']);
        $this->assertEquals(4.2, $content['platforms'][0]['earned']);
        $this->assertEquals(4.2, $content['total']);
    }

    /**
     * отчет по доходам за весь период
     */
    public function testGetBillingEarningsOkLongerPeriod()
    {
        /** @var User $publisher */
        $publisher = self::$parameters['fixtures']['publisher'];

        $url = self::$router->generate('api_get_billing_earnings');

        $data = ['date_from' => '2014-02-22',
                 'date_to'   => '2014-02-23'];

        $this->sendRequest($publisher, 'GET', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->assertCount(1, $content['platforms']);
        $this->assertEquals('111', $content['platforms'][0]['name']);
        $this->assertEquals(3, $content['platforms'][0]['views']);
        $this->assertEquals(6.3, $content['platforms'][0]['earned']);
        $this->assertEquals(6.3, $content['total']);
    }

    /**
     * отчет по заработкам по площадке
     */
    public function testGetBillingEarningsPlatformOk()
    {
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platforms'][0];
        /** @var User $publisher */
        $publisher = self::$parameters['fixtures']['publisher'];

        $url = self::$router->generate('api_get_billing_earnings_by_platform', ['platform_id' => $platform->getId()]);

        $data = ['date_from' => '2014-02-22',
                 'date_to'   => '2014-02-23'];

        $this->sendRequest($publisher, 'GET', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->assertInternalType('array', $content);
        $this->assertArrayHasOnlyKeys(['stats', 'total'], $content);

        $this->assertCount(2, $content['stats']);
        $this->assertArrayHasOnlyKeys(['views', 'date', 'earned'], $content['stats'][0]);

        $this->assertEquals('2014-02-22', $content['stats'][0]['date']);
        $this->assertEquals(1, $content['stats'][0]['views']);
        $this->assertEquals(2.1, $content['stats'][0]['earned']);
        $this->assertEquals('2014-02-23', $content['stats'][1]['date']);
        $this->assertEquals(2, $content['stats'][1]['views']);
        $this->assertEquals(4.2, $content['stats'][1]['earned']);
        $this->assertEquals(6.3, $content['total']);
    }


    /**
     * отчет по заработакам по удалённой площадке
     */
    public function testGetBillingEarningsDeletedPlatformOk()
    {
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platforms'][1];
        /** @var User $publisher */
        $publisher = self::$parameters['fixtures']['publisher'];

        $url = self::$router->generate('api_get_billing_earnings_by_platform', ['platform_id' => $platform->getId()]);

        $data = ['date_from' => '2014-02-22',
                 'date_to'   => '2014-02-23'];

        $this->sendRequest($publisher, 'GET', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
        $this->assertInternalType('array', $content);
        $this->assertArrayHasOnlyKeys(['stats', 'total'], $content);
        $this->assertEquals(0, $content['total']);
    }

    /**
     * отчет по операциям вывода
     */
    public function testGetBillingWithdrawalsOk()
    {
        $publisher = self::$parameters['fixtures']['publisher'];
        $url = self::$router->generate('api_get_billing_withdrawals');

        $data = ['date_from' => '2013-12-10',
                 'date_to'   => '2014-03-01'];

        $this->sendRequest($publisher, 'GET', $url, $data);
        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->assertInternalType('array', $content);
        $this->assertArrayHasOnlyKeys(['withdrawals', 'total'], $content);

        $this->assertCount(4, $content['withdrawals']);
        $this->assertArrayHasOnlyKeys(['type', 'date', 'amount', 'status'], $content['withdrawals'][0]);

        $this->assertEquals('yandex', $content['withdrawals'][0]['type']);
        $this->assertEquals('new', $content['withdrawals'][0]['status']);
        $this->assertEquals(20, $content['withdrawals'][0]['amount']);

        $this->assertEquals('yandex', $content['withdrawals'][1]['type']);
        $this->assertEquals('error', $content['withdrawals'][1]['status']);
        $this->assertEquals(30, $content['withdrawals'][1]['amount']);

        $this->assertEquals('yandex', $content['withdrawals'][2]['type']);
        $this->assertEquals('ok', $content['withdrawals'][2]['status']);
        $this->assertEquals(50, $content['withdrawals'][2]['amount']);

        $this->assertEquals('yandex', $content['withdrawals'][3]['type']);
        $this->assertEquals(100, $content['withdrawals'][3]['amount']);
        $this->assertEquals('ok', $content['withdrawals'][3]['status']);

        $this->assertEquals(150, $content['total']);
    }


    public function getBillingSpendingsErrorsProvider()
    {
        return [
              [
                    [],
                    ['date_from' => 'Значение не должно быть пустым.',
                     'date_to'   => 'Значение не должно быть пустым.',]
              ],
              [
                    ['date_from' => 'aaa',
                     'date_to'   => '2009'],
                    ['date_from' => 'Значение недопустимо.',
                     'date_to'   => 'Значение недопустимо.',
                    ]
              ],
              [
                    ['date_from' => '2009-10',
                     'date_to'   => '20101010'],
                    ['date_from' => 'Значение недопустимо.',
                     'date_to'   => 'Значение недопустимо.',
                    ]
              ],
              [
                    ['date_from' => '2009-13-10',
                     'date_to'   => '2010-02-32'],
                    ['date_from' => 'Значение недопустимо.',
                     'date_to'   => 'Значение недопустимо.',
                    ]
              ],
              [
                    ['date_from' => '2009-12-10',
                     'date_to'   => '2008-02-20'],
                    ['неверный диапазон']
              ]
        ];
    }


    protected static function loadTestFixtures()
    {
        /** @var UserManager $userManager */
        $userManager = self::getContainer()->get('fos_user.user_manager');
        /** @var EntityManager $entityManager */
        $entityManager = self::$em;

        $campaignManager = self::getContainer()->get('vifeed.campaign.manager');

        $tokenManager = static::getContainer()->get('vifeed.user.wsse_token_manager');
        /** @var VideoViewPaymentManager $viewPaymentManager */
        $viewPaymentManager = self::getContainer()->get('vifeed.payment.video_view_payment_manager');

        $paymentPluginController = self::getContainer()->get('payment.plugin_controller');
        /** @var StatsManager $statsManager */
        $statsManager = self::getContainer()->get('vifeed.videoview.stats_manager');

        /** @var User $advertiser */
        $advertiser = $userManager->createUser();
        $advertiser
              ->setEmail('testadvertiser1@vifeed.ru')
              ->setUsername('testadvertiser1@vifeed.ru')
              ->setBalance(26)
              ->setEnabled(true)
              ->setType(User::TYPE_ADVERTISER)
              ->setPlainPassword('12345');

        $userManager->updateUser($advertiser, false);

        /** @var User $publisher */
        $publisher = $userManager->createUser();
        $publisher
              ->setEmail('testpublisher1@vifeed.ru')
              ->setUsername('testpublisher1@vifeed.ru')
              ->setBalance(200)
              ->setEnabled(true)
              ->setType(User::TYPE_PUBLISHER)
              ->setPlainPassword('12345');

        $userManager->updateUser($publisher, false);

        $campaign1 = new Campaign();
        $campaign1
              ->setStatus(Campaign::STATUS_ON)
              ->setBid(3)
              ->setName('111')
              ->setUser($advertiser)
              ->setHash(substr(md5(mt_rand(1, 100)), 0, 11))
              ->setDailyBudget(7)
              ->setBalance(10)
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
              ->setBalance(10)
              ->setGeneralBudget(10)
              ->setDeletedAt(new \DateTime('2014-02-10'));
        $campaignManager->save($campaign2);

        $platform1 = new Platform();
        $platform1
              ->setName('111')
              ->setUser($publisher)
              ->setUrl('111')
              ->setDescription('');
        $entityManager->persist($platform1);

        // плолщадка удалена (soft-delete)
        $platform2 = new Platform();
        $platform2
              ->setName('222')
              ->setUser($publisher)
              ->setUrl('222')
              ->setDescription('')
              ->setDeletedAt(new \DateTime('2014-02-10'));
        $entityManager->persist($platform2);


        $view1 = new VideoView();
        $view1
              ->setCampaign($campaign1)
              ->setPlatform($platform1)
              ->setCurrentTime(60)
              ->setTimestamp((new \DateTime('2014-02-22 00:00:00'))->format('U'))
              ->setTrackNumber(60)
              ->setViewerId(md5(1));
        $entityManager->persist($view1);
        $entityManager->flush();
        $viewPaymentManager->reckon($view1);

        $view2 = new VideoView();
        $view2
              ->setCampaign($campaign1)
              ->setPlatform($platform2)
              ->setCurrentTime(60)
              ->setTimestamp((new \DateTime('2014-02-23 00:00:00'))->format('U'))
              ->setTrackNumber(60)
              ->setViewerId(md5(2));
        $entityManager->persist($view2);
        $entityManager->flush();
        $viewPaymentManager->reckon($view2);

        $view3 = new VideoView();
        $view3
              ->setCampaign($campaign2)
              ->setPlatform($platform1)
              ->setCurrentTime(60)
              ->setTimestamp((new \DateTime('2014-02-23 23:00:00'))->format('U'))
              ->setTrackNumber(60)
              ->setViewerId(md5(3));
        $entityManager->persist($view3);
        $entityManager->flush();
        $viewPaymentManager->reckon($view3);

        $view4 = new VideoView();
        $view4
              ->setCampaign($campaign2)
              ->setPlatform($platform1)
              ->setCurrentTime(60)
              ->setTimestamp((new \DateTime('2014-02-23 23:29:59'))->format('U'))
              ->setTrackNumber(60)
              ->setViewerId(md5(4));
        $entityManager->persist($view4);
        $entityManager->flush();
        $viewPaymentManager->reckon($view4);

        $view5 = new VideoView();
        $view5
              ->setCampaign($campaign2)
              ->setPlatform($platform1)
              ->setCurrentTime(60)
              ->setTimestamp((new \DateTime('2014-02-23 23:59:59'))->format('U'))
              ->setTrackNumber(60)
              ->setViewerId(md5(4));
        $entityManager->persist($view5);
        $entityManager->flush();
        $viewPaymentManager->reckon($view5);

        $instruction1 = new PaymentInstruction(100, 'RUR', 'robokassa');
        $paymentPluginController->createPaymentInstruction($instruction1);

        $instruction2 = new PaymentInstruction(50, 'RUR', 'robokassa');
        $paymentPluginController->createPaymentInstruction($instruction2);

        $instruction3 = new PaymentInstruction(50, 'RUR', 'robokassa');
        $paymentPluginController->createPaymentInstruction($instruction3);

        $order1 = new Order();
        $order1->setUser($advertiser)
               ->setStatus(Order::STATUS_PAID)
               ->setAmount(100)
               ->setPaymentInstruction($instruction1);
        $entityManager->persist($order1);

        $order2 = new Order();
        $order2->setUser($advertiser)
               ->setStatus(Order::STATUS_PAID)
               ->setAmount(50)
               ->setPaymentInstruction($instruction2);
        $entityManager->persist($order2);

        $order3 = new Order();
        $order3->setUser($advertiser)
               ->setStatus(Order::STATUS_NEW)
               ->setAmount(50)->setPaymentInstruction($instruction3);
        $entityManager->persist($order3);

        $wallet = new Wallet();
        $wallet->setUser($publisher)
               ->setNumber(12345)
               ->setType('yandex');
        $entityManager->persist($wallet);

        $withdrawal1 = new Withdrawal();
        $withdrawal1->setUser($publisher)
                    ->setAmount(50)
                    ->setWallet($wallet)
                    ->setCreatedAt(new \DateTime('2014-02-28 10:00:00'))
                    ->setStatus(Withdrawal::STATUS_OK);
        $entityManager->persist($withdrawal1);

        $withdrawal2 = new Withdrawal();
        $withdrawal2->setUser($publisher)
                    ->setAmount(100)
                    ->setWallet($wallet)
                    ->setCreatedAt(new \DateTime('2014-02-28 12:00:00'))
                    ->setStatus(Withdrawal::STATUS_OK);
        $entityManager->persist($withdrawal2);

        $withdrawal3 = new Withdrawal();
        $withdrawal3->setUser($publisher)
                    ->setAmount(20)
                    ->setWallet($wallet)
                    ->setCreatedAt(new \DateTime('2014-02-27 10:00:00'))
                    ->setStatus(Withdrawal::STATUS_CREATED);
        $entityManager->persist($withdrawal3);

        $withdrawal4 = new Withdrawal();
        $withdrawal4->setUser($publisher)
                    ->setAmount(30)
                    ->setWallet($wallet)
                    ->setCreatedAt(new \DateTime('2014-02-27 11:00:00'))
                    ->setStatus(Withdrawal::STATUS_ERROR);
        $entityManager->persist($withdrawal4);

        $entityManager->flush();

        $tokenManager->createUserToken($advertiser->getId());
        $tokenManager->createUserToken($publisher->getId());

        $statsManager->recollectAllStats();
        $statsManager->collectDailyStats((new \DateTime())->setTime(0, 0, 0));

        return array(
              'advertiser' => $advertiser,
              'publisher'  => $publisher,
              'platforms'  => [$platform1, $platform2],
              'campaigns'  => array(
                    $campaign1,
                    $campaign2,
              ),
        );
    }
}
 