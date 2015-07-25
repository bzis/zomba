<?php
namespace Vifeed\PaymentBundle\Tests\Unit;

use Doctrine\ORM\EntityManager;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\PlatformBundle\Entity\Platform;
use Vifeed\SystemBundle\Tests\ApiTestCase;
use Vifeed\SystemBundle\Tests\TestCase;
use Vifeed\UserBundle\Entity\User;
use Vifeed\VideoViewBundle\Entity\VideoView;

class VideoViewPaymentManagerReckonTest extends ApiTestCase
{

    /**
     * короткий просмотр не оплачивается
     */
    public function testShortView()
    {
        /** @var VideoView $view */
        $view = self::$parameters['fixtures']['views'][0];
        $campaign = $view->getCampaign();
        $totalViews = $campaign->getTotalViews();
        $budgetUsed = $campaign->getGeneralBudgetUsed();

        $this->assertTrue($this->getPaymentManager()->reckon($view));
        self::$em->refresh($view);
        self::$em->refresh($campaign);

        $this->assertFalse($view->getIsPaid());
        $this->assertEquals($totalViews + 1, $campaign->getTotalViews());
        $this->assertEquals($budgetUsed, $campaign->getGeneralBudgetUsed());
    }

    /**
     * короткий просмотр в короткой кампании оплачивается
     */
    public function testShortViewShortCampaign()
    {
        /** @var VideoView $view */
        $view = self::$parameters['fixtures']['views'][1];
        $campaign = $view->getCampaign();
        $budgetUsed = $campaign->getGeneralBudgetUsed();

        $this->assertTrue($this->getPaymentManager()->reckon($view));
        self::$em->refresh($view);
        self::$em->refresh($campaign);

        $this->assertTrue($view->getIsPaid());
        $this->assertEquals($budgetUsed + $campaign->getBid(), $campaign->getGeneralBudgetUsed());
    }

    /**
     * Был короткий неоплаченный просмотр. Повторный - длинный - оплачивается
     *
     * @depends testShortView
     */
    public function testRepeatedView1()
    {
        /** @var VideoView $view */
        $view = self::$parameters['fixtures']['views'][2];

        $this->assertTrue($this->getPaymentManager()->reckon($view));
        self::$em->refresh($view);

        $this->assertTrue($view->getIsPaid());
    }

    /**
     * Был оплаченный просмотр. Повторный не оплачивается
     *
     * @depends testShortViewShortCampaign
     */
    public function testRepeatedView2()
    {
        /** @var VideoView $view */
        $view = self::$parameters['fixtures']['views'][3];

        $this->assertTrue($this->getPaymentManager()->reckon($view));
        self::$em->refresh($view);

        $this->assertFalse($view->getIsPaid());
    }

    /**
     * Был оплаченный просмотр. Повторный оплачивается, если прошло больше 30 дней
     *
     * @depends testShortView
     */
    public function testRepeatedView3()
    {
        /** @var VideoView $view */
        $view = self::$parameters['fixtures']['views'][4];

        $this->assertTrue($this->getPaymentManager()->reckon($view));
        self::$em->refresh($view);

        $this->assertTrue($view->getIsPaid());
    }

    /**
     * тест фильтра по ip на общую активность ip-адреса по всем кампаниям
     */
    public function testIpFilter()
    {
        $view5 = self::$parameters['fixtures']['views'][5];
        $view6 = self::$parameters['fixtures']['views'][6];
        $view7 = self::$parameters['fixtures']['views'][7];
        $view8 = self::$parameters['fixtures']['views'][8];
        $view9 = self::$parameters['fixtures']['views'][9];
        $view10 = self::$parameters['fixtures']['views'][10];
        $view11 = self::$parameters['fixtures']['views'][11];
        $view12 = self::$parameters['fixtures']['views'][12];

        // 1-й просмотр с одного ip
        $this->assertTrue($this->getPaymentManager()->reckon($view5));
        self::$em->refresh($view5);
        $this->assertTrue($view5->getIsPaid());

        // 2-й просмотр с одного ip, через 7 минут, не оплачивается
        $this->assertTrue($this->getPaymentManager()->reckon($view6));
        self::$em->refresh($view6);
        $this->assertFalse($view6->getIsPaid());

        // 3-й просмотр с одного ip, через 10 минут, оплачивается
        $this->assertTrue($this->getPaymentManager()->reckon($view7));
        self::$em->refresh($view7);
        $this->assertTrue($view7->getIsPaid());

        // 4-й просмотр с одного ip, через 20 минут, оплачивается
        $this->assertTrue($this->getPaymentManager()->reckon($view8));
        self::$em->refresh($view8);
        $this->assertTrue($view8->getIsPaid());

        // 5-й просмотр с одного ip, через 30 минут, оплачивается
        $this->assertTrue($this->getPaymentManager()->reckon($view9));
        self::$em->refresh($view9);
        $this->assertTrue($view9->getIsPaid());

        // 6-й просмотр с одного ip, через 40 минут, оплачивается
        $this->assertTrue($this->getPaymentManager()->reckon($view10));
        self::$em->refresh($view10);
        $this->assertTrue($view10->getIsPaid());

        // 8-й просмотр с одного ip, через 10 часов, не оплачивается
        $this->assertTrue($this->getPaymentManager()->reckon($view11));
        self::$em->refresh($view11);
        $this->assertFalse($view11->getIsPaid());

        // 9-й просмотр с одного ip, через сутки, оплачивается
        $this->assertTrue($this->getPaymentManager()->reckon($view12));
        self::$em->refresh($view12);
        $this->assertTrue($view12->getIsPaid());
    }

    /**
     * тест фильтра по ip на общую активность ip-адреса по одной кампании
     */
    public function testIpFilter2()
    {
        $view13 = self::$parameters['fixtures']['views'][13];
        $view14 = self::$parameters['fixtures']['views'][14];

        // 1-й просмотр с одного ip
        $this->assertTrue($this->getPaymentManager()->reckon($view13));
        self::$em->refresh($view13);
        $this->assertTrue($view13->getIsPaid());

        // 2-й просмотр с одного ip той же кампании, год спустя, не оплачивается
        $this->assertTrue($this->getPaymentManager()->reckon($view14));
        self::$em->refresh($view14);
        $this->assertFalse($view14->getIsPaid());
    }

    /**
     * @return \Vifeed\PaymentBundle\Manager\VideoViewPaymentManager
     */
    protected function getPaymentManager()
    {
        return self::$container->get('vifeed.payment.video_view_payment_manager');
    }

    protected static function loadTestFixtures()
    {
        /** @var EntityManager $entityManager */
        $entityManager = self::$em;

        $userManager = self::$container->get('fos_user.user_manager');

        $advertiser = new User();
        $advertiser->setType(User::TYPE_ADVERTISER)
                   ->setEmail('testadv1@vifeed.co')
                   ->setUsername('testadv1@vifeed.co')
                   ->setEnabled(true)
                   ->setPlainPassword('12345');
        $userManager->updateCanonicalFields($advertiser);

        $publisher = new User();
        $publisher->setType(User::TYPE_PUBLISHER)
                  ->setEmail('testpub1@vifeed.co')
                  ->setEnabled(true)
                  ->setUsername('testpub1@vifeed.co')
                  ->setPlainPassword('12345');
        $userManager->updateCanonicalFields($publisher);

        $campaign1 = new Campaign();
        $campaign1->setUser($advertiser)
                  ->setName('111')
                  ->setBid(1)
                  ->setGeneralBudget(100)
                  ->setBalance(100)
                  ->setDailyBudget(0)
                  ->setStatus(Campaign::STATUS_ON)
                  ->setHash('123');

        $campaign2 = new Campaign();
        $campaign2->setUser($advertiser)
                  ->setName('222')
                  ->setBid(1)
                  ->setGeneralBudget(100)
                  ->setBalance(100)
                  ->setDailyBudget(0)
                  ->setHash('123')
                  ->setStatus(Campaign::STATUS_ON)
                  ->setYoutubeData('duration', 4);

        $campaigns = [];
        for ($i = 0; $i < 8; $i++) {
            $campaigns[$i] = new Campaign();
            $campaigns[$i]->setUser($advertiser)
                          ->setName($i)
                          ->setBid(1)
                          ->setGeneralBudget(100)
                          ->setBalance(100)
                          ->setDailyBudget(0)
                          ->setStatus(Campaign::STATUS_ON)
                          ->setHash('123' . $i);
            $entityManager->persist($campaigns[$i]);
        }

        $platform1 = new Platform();
        $platform1->setUser($publisher)
                  ->setUrl('123')
                  ->setName('111')
                  ->setDescription('123');

        $view0 = new VideoView();
        $view0
              ->setCampaign($campaign1)
              ->setPlatform($platform1)
              ->setCurrentTime(10)
              ->setTimestamp((new \DateTime('yesterday'))->setTime(0, 0, 0)->format('U'))
              ->setTrackNumber(10)
              ->setIp(11111)
              ->setViewerId(md5(1));

        $view1 = new VideoView();
        $view1
              ->setCampaign($campaign2)
              ->setPlatform($platform1)
              ->setCurrentTime(3)
              ->setTimestamp((new \DateTime('yesterday'))->setTime(0, 0, 0)->format('U'))
              ->setTrackNumber(3)
              ->setIp(11112)
              ->setViewerId(md5(1));

        $view2 = new VideoView();
        $view2
              ->setCampaign($campaign1)
              ->setPlatform($platform1)
              ->setCurrentTime(50)
              ->setTimestamp((new \DateTime('-35 day'))->setTime(0, 0, 0)->format('U'))
              ->setTrackNumber(50)
              ->setIp(11113)
              ->setViewerId(md5(1));

        $view3 = new VideoView();
        $view3
              ->setCampaign($campaign2)
              ->setPlatform($platform1)
              ->setCurrentTime(3)
              ->setTimestamp((new \DateTime('yesterday'))->setTime(0, 0, 0)->format('U'))
              ->setTrackNumber(3)
              ->setIp(11114)
              ->setViewerId(md5(1));

        $view4 = new VideoView();
        $view4
              ->setCampaign($campaign1)
              ->setPlatform($platform1)
              ->setCurrentTime(39)
              ->setTimestamp((new \DateTime('yesterday'))->setTime(0, 0, 0)->format('U'))
              ->setTrackNumber(39)
              ->setIp(11115)
              ->setViewerId(md5(1));

        // для теста на ip-filter1 - начало
        $view5 = new VideoView();
        $view5
              ->setCampaign($campaigns[0])
              ->setPlatform($platform1)
              ->setCurrentTime(50)
              ->setTimestamp((new \DateTime('yesterday'))->setTime(0, 0, 1)->format('U'))
              ->setTrackNumber(50)
              ->setIp(11116)
              ->setViewerId(md5(2));

        $view6 = new VideoView();
        $view6
              ->setCampaign($campaigns[1])
              ->setPlatform($platform1)
              ->setCurrentTime(50)
              ->setTimestamp((new \DateTime('yesterday'))->setTime(0, 7, 1)->format('U'))
              ->setTrackNumber(50)
              ->setIp(11116)
              ->setViewerId(md5(3));

        $view7 = new VideoView();
        $view7
              ->setCampaign($campaigns[2])
              ->setPlatform($platform1)
              ->setCurrentTime(50)
              ->setTimestamp((new \DateTime('yesterday'))->setTime(0, 10, 2)->format('U'))
              ->setTrackNumber(50)
              ->setIp(11116)
              ->setViewerId(md5(4));

        $view8 = new VideoView();
        $view8
              ->setCampaign($campaigns[3])
              ->setPlatform($platform1)
              ->setCurrentTime(50)
              ->setTimestamp((new \DateTime('yesterday'))->setTime(0, 20, 3)->format('U'))
              ->setTrackNumber(50)
              ->setIp(11116)
              ->setViewerId(md5(5));

        $view9 = new VideoView();
        $view9
              ->setCampaign($campaigns[4])
              ->setPlatform($platform1)
              ->setCurrentTime(50)
              ->setTimestamp((new \DateTime('yesterday'))->setTime(0, 30, 4)->format('U'))
              ->setTrackNumber(50)
              ->setIp(11116)
              ->setViewerId(md5(6));

        $view10 = new VideoView();
        $view10
              ->setCampaign($campaigns[5])
              ->setPlatform($platform1)
              ->setCurrentTime(50)
              ->setTimestamp((new \DateTime('yesterday'))->setTime(0, 40, 5)->format('U'))
              ->setTrackNumber(50)
              ->setIp(11116)
              ->setViewerId(md5(7));

        $view11 = new VideoView();
        $view11
              ->setCampaign($campaigns[6])
              ->setPlatform($platform1)
              ->setCurrentTime(50)
              ->setTimestamp((new \DateTime('yesterday'))->setTime(10, 0, 0)->format('U'))
              ->setTrackNumber(50)
              ->setIp(11116)
              ->setViewerId(md5(8));

        $view12 = new VideoView();
        $view12
              ->setCampaign($campaigns[7])
              ->setPlatform($platform1)
              ->setCurrentTime(50)
              ->setTimestamp((new \DateTime('today'))->setTime(0, 40, 6)->format('U'))
              ->setTrackNumber(50)
              ->setIp(11116)
              ->setViewerId(md5(9));
        // для теста на ip-filter1 - конец

        // для теста на ip-filter2 - начало
        $view13 = new VideoView();
        $view13
              ->setCampaign($campaign1)
              ->setPlatform($platform1)
              ->setCurrentTime(50)
              ->setTimestamp((new \DateTime('-1 year'))->setTime(0, 0, 0)->format('U'))
              ->setTrackNumber(50)
              ->setIp(11117)
              ->setViewerId(md5(10));

        $view14 = new VideoView();
        $view14
              ->setCampaign($campaign1)
              ->setPlatform($platform1)
              ->setCurrentTime(50)
              ->setTimestamp((new \DateTime('today'))->setTime(0, 0, 0)->format('U'))
              ->setTrackNumber(50)
              ->setIp(11117)
              ->setViewerId(md5(11));
        // для теста на ip-filter2 - конец


        $entityManager->persist($advertiser);
        $entityManager->persist($publisher);
        $entityManager->persist($campaign1);
        $entityManager->persist($campaign2);
        $entityManager->persist($platform1);
        $entityManager->persist($view0);
        $entityManager->persist($view1);
        $entityManager->persist($view2);
        $entityManager->persist($view3);
        $entityManager->persist($view4);
        $entityManager->persist($view5);
        $entityManager->persist($view6);
        $entityManager->persist($view7);
        $entityManager->persist($view8);
        $entityManager->persist($view9);
        $entityManager->persist($view10);
        $entityManager->persist($view11);
        $entityManager->persist($view12);
        $entityManager->persist($view13);
        $entityManager->persist($view14);

        $entityManager->flush();

        $fixtures = [
              'advertiser' => $advertiser,
              'publisher'  => $publisher,
              'campaigns'  => array_merge([$campaign1, $campaign2], $campaigns),
              'platofrms'  => [$platform1],
              'views'      => [$view0, $view1, $view2, $view3, $view4, $view5, $view6, $view7, $view8, $view9, $view10, $view11, $view12,
                               $view13, $view14]
        ];

        return $fixtures;
    }


}
 