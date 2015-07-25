<?php
namespace Vifeed\PaymentBundle\Tests\Command;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Doctrine\UserManager;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\PlatformBundle\Entity\Platform;
use Vifeed\SystemBundle\Tests\ApiTestCase;
use Vifeed\UserBundle\Entity\User;
use Vifeed\VideoViewBundle\Entity\VideoView;

class SurchargeVideoViewCommandTest extends ApiTestCase
{

    /**
     *
     */
    public function testCommand()
    {
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platform'];
        /** @var Campaign[] $campaigns */
        $campaigns = self::$parameters['fixtures']['campaigns'];
        $views = [];

        $views[0] = $this->generateVideoView($campaigns[0], $platform);
        $views[1] = $this->generateVideoView($campaigns[1], $platform);
        $views[2] = $this->generateVideoView($campaigns[2], $platform, ['current_time' => 10]); // просмотрено только 10 секунд
        $views[3] = $this->generateVideoView($campaigns[2], $platform);
        $views[4] = $this->generateVideoView($campaigns[2], $platform);
        $views[5] = $this->generateVideoView($campaigns[3], $platform);
        $views[6] = $this->generateVideoView($campaigns[3], $platform);
        $views[7] = $this->generateVideoView($campaigns[4], $platform);
        $views[8] = $this->generateVideoView($campaigns[4], $platform);

        $this->runCommand('vifeed:payment:surcharge-views');

        $this->refreshObjects($campaigns, $views);

        $this->assertEquals(10, $campaigns[0]->getBalance());
        $this->assertEquals(false, $views[0]->getIsPaid());
        $this->assertEquals(Campaign::STATUS_ON, $campaigns[0]->getStatus());

        $this->assertEquals(1, $campaigns[1]->getBalance());
        $this->assertEquals(false, $views[1]->getIsPaid());
        $this->assertEquals(Campaign::STATUS_AWAITING, $campaigns[1]->getStatus());

        $this->assertEquals(97, $campaigns[2]->getBalance());
        $this->assertEquals(0, $campaigns[2]->getDailyBudgetRemains());
        $this->assertEquals(false, $views[2]->getIsPaid());
        $this->assertEquals(true, $views[3]->getIsPaid());
        $this->assertEquals(false, $views[4]->getIsPaid());
        $this->assertEquals(Campaign::STATUS_AWAITING, $campaigns[2]->getStatus());

        $this->assertEquals(2, $campaigns[3]->getBalance());
        $this->assertEquals(true, $views[5]->getIsPaid());
        $this->assertEquals(false, $views[6]->getIsPaid());
        $this->assertEquals(Campaign::STATUS_AWAITING, $campaigns[3]->getStatus());

        $this->assertEquals(0, $campaigns[4]->getBalance());
        $this->assertEquals(true, $views[7]->getIsPaid());
        $this->assertEquals(false, $views[8]->getIsPaid());
        $this->assertEquals(Campaign::STATUS_ENDED, $campaigns[4]->getStatus());
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
              ->setBalance(0)
              ->setEnabled(true)
              ->setType(User::TYPE_PUBLISHER)
              ->setPlainPassword('12345');

        $userManager->updateUser($publisher, false);

        $campaign0 = new Campaign();
        $campaign0
              ->setStatus(Campaign::STATUS_ON)
              ->setBid(3)
              ->setName('000')
              ->setUser($advertiser)
              ->setHash(substr(md5(mt_rand(1, 100)), 0, 11))
              ->setDailyBudget(6)
              ->setGeneralBudget(10)
              ->setBalance(10);
        $campaignManager->save($campaign0);

        $campaign1 = new Campaign();
        $campaign1
              ->setStatus(Campaign::STATUS_AWAITING)
              ->setBid(3)
              ->setName('111')
              ->setUser($advertiser)
              ->setHash(substr(md5(mt_rand(1, 100)), 0, 11))
              ->setDailyBudget(0)
              ->setGeneralBudget(10)
              ->setBalance(1);
        $campaignManager->save($campaign1);

        $campaign2 = new Campaign();
        $campaign2
              ->setStatus(Campaign::STATUS_AWAITING)
              ->setBid(3)
              ->setName('222')
              ->setHash(substr(md5(mt_rand(1, 100)), 0, 11))
              ->setUser($advertiser)
              ->setDailyBudget(10)
              ->updateDailyBudgetUsed(7)
              ->setGeneralBudget(100)
              ->setBalance(100);
        $campaignManager->save($campaign2);

        $campaign3 = new Campaign();
        $campaign3
              ->setStatus(Campaign::STATUS_AWAITING)
              ->setBid(3)
              ->setName('333')
              ->setHash(substr(md5(mt_rand(1, 100)), 0, 11))
              ->setUser($advertiser)
              ->setDailyBudget(0)
              ->setGeneralBudget(100)
              ->setBalance(5);
        $campaignManager->save($campaign3);

        $campaign4 = new Campaign();
        $campaign4
              ->setStatus(Campaign::STATUS_ENDED)
              ->setBid(3)
              ->setName('444')
              ->setHash(substr(md5(mt_rand(1, 100)), 0, 11))
              ->setUser($advertiser)
              ->setDailyBudget(0)
              ->setGeneralBudget(100)
              ->setBalance(3);
        $campaignManager->save($campaign4);

        $platform = new Platform();
        $platform
              ->setName('111')
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
                    $campaign0,
                    $campaign1,
                    $campaign2,
                    $campaign3,
                    $campaign4
              ),
        );
    }
}
 