<?php

namespace Vifeed\CampaignBundle\Tests\Integration;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Model\UserManager;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\PlatformBundle\Entity\Platform;
use Vifeed\VideoViewBundle\Entity\VideoView;
use Vifeed\SystemBundle\Tests\ApiTestCase;
use Vifeed\UserBundle\Entity\User;

class CampaignStatusTest extends ApiTestCase
{

    /**
     * сценарий: на балансе пользователя есть деньги, кампании включены
     * фикстуры: LoadCampaignStatusTestData
     */
    public function testCampaignStatusOn2Awaiting()
    {
        $paymentManager = $this->getContainer()->get('vifeed.payment.video_view_payment_manager');
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platform'];
        /** @var Campaign[] $campaigns */
        $campaigns = self::$parameters['fixtures']['campaigns'];
        /** @var User $advertiser */
        $advertiser = self::$parameters['fixtures']['advertiser'];
        /** @var User $publisher */
        $publisher = self::$parameters['fixtures']['publisher'];
        $advertiserBalanceBefore = $advertiser->getBalance();
        $publisherBalanceBefore = $publisher->getBalance();
        $constants = static::getContainer()->getParameter('vifeed');
        $comission = $constants['comission'];
        $delta = $constants['delta'];

        $paymentManager->reckon($this->generateVideoView($campaigns[0], $platform));
        $paymentManager->reckon($this->generateVideoView($campaigns[1], $platform));
        $paymentManager->reckon($this->generateVideoView($campaigns[1], $platform, ['current_time' => 10])); // просмотрено только 10 секунд
        $paymentManager->reckon($this->generateVideoView($campaigns[2], $platform));
        $paymentManager->reckon($this->generateVideoView($campaigns[3], $platform));

        $this->refreshObjects($campaigns, $advertiser, $publisher);

        // выставлены правильные статусы
        $this->assertEquals(Campaign::STATUS_AWAITING, $campaigns[0]->getStatus());
        $this->assertEquals(Campaign::STATUS_ON, $campaigns[1]->getStatus());
        $this->assertEquals(Campaign::STATUS_ON, $campaigns[2]->getStatus());
        $this->assertEquals(Campaign::STATUS_ON, $campaigns[3]->getStatus());

        // апдейтится текущий расход средств
        $sum = 0;
        foreach ($campaigns as $campaign) {
            $sum += $campaign->getGeneralBudgetUsed();
            $this->assertEquals(
                 $campaign->getGeneralBudget() - $campaign->getBid(),
                 $campaign->getBalance()
            );
            if ($campaign->hasDailyBudgetLimit()) {
                $this->assertEquals(
                     $campaign->getDailyBudget() - $campaign->getBid(),
                     $campaign->getDailyBudgetRemains()
                );
            }
        }

        // паблишеру зачислена правильная сумма
        $this->assertEquals($publisherBalanceBefore + ($sum * (1 - $comission)), $publisher->getBalance());

        $paymentManager->reckon($this->generateVideoView($campaigns[1], $platform));
        $paymentManager->reckon($this->generateVideoView($campaigns[1], $platform));
        $paymentManager->reckon($this->generateVideoView($campaigns[2], $platform));
        $paymentManager->reckon($this->generateVideoView($campaigns[3], $platform));

        $this->refreshObjects($campaigns, $advertiser, $publisher);

        // выставлены правильные статусы
        $this->assertEquals(Campaign::STATUS_ENDED, $campaigns[1]->getStatus());
        $this->assertEquals(Campaign::STATUS_AWAITING, $campaigns[2]->getStatus());
        $this->assertEquals(Campaign::STATUS_ON, $campaigns[3]->getStatus());

        // причины отключения
        // отключена по дневному бюджету
        $this->assertLessThan(round(($delta + 1) * $campaigns[0]->getBid(), 2), $campaigns[0]->getDailyBudgetRemains());
        // отключена по общему бюджету
        $this->assertLessThan(round(($delta + 1) * $campaigns[1]->getBid(), 2), $campaigns[1]->getBalance());
        // отключена по дневному бюджету
        $this->assertLessThan(round(($delta + 1) * $campaigns[2]->getBid(), 2), $campaigns[2]->getDailyBudgetRemains());
        // ограничений не было, отключена по балансу юзера
        $this->assertGreaterThanOrEqual(round(($delta + 1) * $campaigns[3]->getBid(), 2), $campaigns[3]->getBalance());
    }


    public function testCampaignStatusAfterDailyUsageReset()
    {
        /** @var Campaign[] $campaigns */
        $campaigns = self::$parameters['fixtures']['campaigns'];

        $campaignIds = array();
        foreach ($campaigns as $campaign) {
            $campaignIds[] = $campaign->getId();
        }

        $this->runCommand('vifeed:campaign:refresh-daily-budget-usage');

        // refresh не работает, видимо, потому, что в новом тесте новый контейнер
        $campaigns = $this->getEntityManager()->getRepository('VifeedCampaignBundle:Campaign')
                          ->findBy(array('id' => $campaignIds));

        // проверяем, что статус не изменился, потому что нет денег на балансе и возвращаем всё в зад,
        // чтобы проверить ещё раз после пополнения баланса
        foreach ($campaigns as $campaign) {
            $this->assertEquals(0, $campaign->getDailyBudgetUsed());
        }

        $this->assertEquals(Campaign::STATUS_ON, $campaigns[0]->getStatus());
        $this->assertEquals(Campaign::STATUS_ENDED, $campaigns[1]->getStatus());
        $this->assertEquals(Campaign::STATUS_ON, $campaigns[2]->getStatus());
        $this->assertEquals(Campaign::STATUS_ON, $campaigns[3]->getStatus());

        $query = 'UPDATE campaign SET daily_budget_used = daily_budget WHERE id IN (' . join(', ', $campaignIds) . ')';
        $this->getEntityManager()->getConnection()->executeUpdate($query);

    }

    /**
     * @param Campaign $campaign
     * @param Platform $platform
     * @param array    $parameters
     *
     * @return VideoView
     */
    private function generateVideoView(Campaign $campaign, Platform $platform, $parameters = array())
    {
        static $i = 0;
        $view = new VideoView();
        $view
              ->setCampaign($campaign)
              ->setPlatform($platform)
              ->setCurrentTime(isset($parameters['current_time']) ? $parameters['current_time'] : 60)
              ->setTimestamp(time())
              ->setTrackNumber(isset($parameters['current_time']) ? $parameters['current_time'] : 60)
              ->setViewerId(md5($i));

        $em = self::$em;
        $em->persist($view);
        $em->flush();

        $i++;

        return $view;
    }

    /**
     *
     * @return array
     */
    protected static function loadTestFixtures()
    {
        /** @var UserManager $userManager */
        $userManager = self::getContainer()->get('fos_user.user_manager');
        /** @var EntityManager $entityManager */
        $entityManager = self::$em;

        $campaignManager = self::getContainer()->get('vifeed.campaign.manager');

        $tokenManager = static::getContainer()->get('vifeed.user.wsse_token_manager');

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
              ->setBalance(0)
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
              ->setDailyBudget(6)
              ->setGeneralBudget(10)
        ->setBalance(10);
        $campaignManager->save($campaign1);

        $campaign2 = new Campaign();
        $campaign2
              ->setStatus(Campaign::STATUS_ON)
              ->setBid(3)
              ->setName('222')
              ->setUser($advertiser)
              ->setHash(substr(md5(mt_rand(1, 100)), 0, 11))
              ->setDailyBudget(0)
              ->setGeneralBudget(10)
              ->setBalance(10);
        $campaignManager->save($campaign2);

        $campaign3 = new Campaign();
        $campaign3
              ->setStatus(Campaign::STATUS_ON)
              ->setBid(3)
              ->setName('333')
              ->setHash(substr(md5(mt_rand(1, 100)), 0, 11))
              ->setUser($advertiser)
              ->setDailyBudget(10)
              ->setGeneralBudget(100)
              ->setBalance(100);
        $campaignManager->save($campaign3);

        $campaign4 = new Campaign();
        $campaign4
              ->setStatus(Campaign::STATUS_ON)
              ->setBid(3)
              ->setName('444')
              ->setHash(substr(md5(mt_rand(1, 100)), 0, 11))
              ->setUser($advertiser)
              ->setDailyBudget(0)
              ->setGeneralBudget(100)
              ->setBalance(100);
        $campaignManager->save($campaign4);

        $platform = new Platform();
        $platform
              ->setName('campaignStatusTestPlatform')
              ->setUser($publisher)
              ->setUrl('')
              ->setDescription('');
        $entityManager->persist($platform);

        $entityManager->flush();

        $tokenManager->createUserToken($advertiser->getId());

        return array(
              'advertiser' => $advertiser,
              'publisher'  => $publisher,
              'platform'   => $platform,
              'campaigns'  => array(
                    $campaign1,
                    $campaign2,
                    $campaign3,
                    $campaign4
              ),
        );
    }


}
 