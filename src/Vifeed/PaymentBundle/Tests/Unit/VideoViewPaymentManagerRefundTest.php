<?php
namespace Vifeed\PaymentBundle\Tests\Unit;

use Doctrine\ORM\EntityManager;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\PlatformBundle\Entity\Platform;
use Vifeed\SystemBundle\Tests\ApiTestCase;
use Vifeed\UserBundle\Entity\User;
use Vifeed\VideoViewBundle\Entity\VideoView;

class VideoViewPaymentManagerRefundTest extends ApiTestCase
{

    /**
     *
     */
    public function testRefundByPublisher()
    {
        /** @var User $publisher */
        $publisher = self::$parameters['fixtures']['publisher'];
        /** @var User $advertiser0 */
        $advertiser0 = self::$parameters['fixtures']['advertisers'][0];
        /** @var User $advertiser1 */
        $advertiser1 = self::$parameters['fixtures']['advertisers'][1];
        /** @var Campaign $campaign0 */
        $campaign0 = self::$parameters['fixtures']['campaigns'][0];
        /** @var Campaign $campaign1 */
        $campaign1 = self::$parameters['fixtures']['campaigns'][1];

        $paymentManager = $this->getPaymentManager();
        $this->assertEquals(90, $campaign0->getBalance());
        $this->assertEquals(80, $campaign1->getBalance());
        $this->assertEquals(0, $advertiser0->getBalance());
        $this->assertEquals(0, $advertiser1->getBalance());
        $this->assertEquals(21, $publisher->getBalance());

        $refund = $paymentManager->refundAllFromPublisher($publisher);
        $this->assertInternalType('array', $refund);
        $this->assertArrayHasOnlyKeys(['paid', 'charged'], $refund);
        $this->assertEquals(30, $refund['paid']);
        $this->assertEquals(21, $refund['charged']);

        self::$em->refresh($publisher);
        self::$em->refresh($advertiser1);
        self::$em->refresh($advertiser0);
        self::$em->refresh($campaign1);
        self::$em->refresh($campaign0);

        $this->assertEquals(90, $campaign0->getBalance());
        $this->assertEquals(80, $campaign1->getBalance());
        $this->assertEquals(10, $advertiser0->getBalance());
        $this->assertEquals(20, $advertiser1->getBalance());
        $this->assertEquals(0, $publisher->getBalance());

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
        $paymentManager = self::$container->get('vifeed.payment.video_view_payment_manager');

        $userManager = self::$container->get('fos_user.user_manager');

        $advertiser0 = new User();
        $advertiser0->setType(User::TYPE_ADVERTISER)
                    ->setEmail('testadv1@vifeed.co')
                    ->setUsername('testadv1@vifeed.co')
                    ->setEnabled(true)
                    ->setBalance(0)
                    ->setPlainPassword('12345');
        $userManager->updateCanonicalFields($advertiser0);

        $advertiser1 = new User();
        $advertiser1->setType(User::TYPE_ADVERTISER)
                    ->setEmail('testadv2@vifeed.co')
                    ->setUsername('testadv2@vifeed.co')
                    ->setEnabled(true)
                    ->setBalance(0)
                    ->setPlainPassword('12345');
        $userManager->updateCanonicalFields($advertiser1);

        $publisher = new User();
        $publisher->setType(User::TYPE_PUBLISHER)
                  ->setEmail('testpub1@vifeed.co')
                  ->setUsername('testpub1@vifeed.co')
                  ->setBalance(0)
                  ->setEnabled(true)
                  ->setPlainPassword('12345');
        $userManager->updateCanonicalFields($publisher);

        $campaign0 = new Campaign();
        $campaign0->setUser($advertiser0)
                  ->setName('111')
                  ->setBid(1)
                  ->setGeneralBudget(100)
                  ->setBalance(100)
                  ->setDailyBudget(0)
                  ->setStatus(Campaign::STATUS_ON)
                  ->setHash('123');

        $campaign1 = new Campaign();
        $campaign1->setUser($advertiser1)
                  ->setName('222')
                  ->setBid(2)
                  ->setGeneralBudget(100)
                  ->setBalance(100)
                  ->setDailyBudget(0)
                  ->setHash('123')
                  ->setStatus(Campaign::STATUS_ON);

        $platform0 = new Platform();
        $platform0->setUser($publisher)
                  ->setUrl('123')
                  ->setName('111')
                  ->setDescription('123');

        $entityManager->persist($advertiser0);
        $entityManager->persist($advertiser1);
        $entityManager->persist($publisher);
        $entityManager->persist($campaign0);
        $entityManager->persist($campaign1);
        $entityManager->persist($platform0);

        $views = [];
        for ($i = 0; $i < 10; $i++) {

            $views[$i] = new VideoView();
            $views[$i]
                  ->setCampaign($campaign0)
                  ->setPlatform($platform0)
                  ->setCurrentTime(30)
                  ->setTimestamp((new \DateTime('yesterday'))->setTime(0, 0, 0)->format('U'))
                  ->setTrackNumber(30)
                  ->setIp(11111 + $i)
                  ->setViewerId(md5($i));
            $entityManager->persist($views[$i]);


            $views[$i + 10] = new VideoView();
            $views[$i + 10]
                  ->setCampaign($campaign1)
                  ->setPlatform($platform0)
                  ->setCurrentTime(30)
                  ->setTimestamp((new \DateTime('yesterday'))->setTime(0, 0, 0)->format('U'))
                  ->setTrackNumber(30)
                  ->setIp(11111 + $i + 10)
                  ->setViewerId(md5($i + 10));
            $entityManager->persist($views[$i + 10]);
        }


        $entityManager->flush();
        foreach ($views as $view) {
            $paymentManager->reckon($view);
        }

        self::$em->refresh($publisher);
        self::$em->refresh($advertiser0);
        self::$em->refresh($advertiser1);
        self::$em->refresh($campaign0);
        self::$em->refresh($campaign1);

        $fixtures = [
              'advertisers' => [$advertiser0, $advertiser1],
              'publisher'   => $publisher,
              'campaigns'   => [$campaign0, $campaign1],
              'platofrms'   => [$platform0],
              'views'       => $views
        ];

        return $fixtures;
    }


}
 