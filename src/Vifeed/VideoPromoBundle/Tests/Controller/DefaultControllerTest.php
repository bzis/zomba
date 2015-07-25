<?php
namespace Vifeed\VideoPromoBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\PlatformBundle\Entity\Platform;
use Vifeed\SystemBundle\Tests\ApiTestCase;
use Vifeed\UserBundle\Entity\User;

class DefaultControllerTest extends ApiTestCase
{

    /**
     * незабаненные рекламодатель и паблишер
     */
    public function testUnbannedUsersShowOk()
    {
        $url = self::$router->generate('vifeed_video_promo_homepage', [
              'platformHash' => 'p1',
              'campaignHash' => 'c1',
              'domain'       => 'zomba.me'
        ]);

        self::$client->request('GET', $url);
        $response = self::$client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * забаненный паблишер, незабаненный рекламодатель
     */
    public function testBannedPublisherUnbannedAdvertiser404()
    {
        $url = self::$router->generate('vifeed_video_promo_homepage', [
              'platformHash' => 'p2',
              'campaignHash' => 'c1',
              'domain'       => 'zomba.me'
        ]);

        self::$client->request('GET', $url);
        $response = self::$client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * забаненный рекламодатель, незабаненный паблишер
     */
    public function testBannedAdvertiserUnbannedPublisher404()
    {
        $url = self::$router->generate('vifeed_video_promo_homepage', [
              'platformHash' => 'p1',
              'campaignHash' => 'c2',
              'domain'       => 'zomba.me'
        ]);

        self::$client->request('GET', $url);
        $response = self::$client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
    }


    /**
     * @return array
     */
    protected static function loadTestFixtures()
    {
        /** @var EntityManager $entityManager */
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $userManager = self::getContainer()->get('fos_user.user_manager');
        $tokenManager = static::getContainer()->get('vifeed.user.wsse_token_manager');

        /** @var User $advertiser1 */
        $advertiser1 = $userManager->createUser();
        $advertiser1
              ->setEmail('testadvertiser1@vifeed.ru')
              ->setUsername('testadvertiser1@vifeed.ru')
              ->setBalance(100)
              ->setEnabled(true)
              ->setType(User::TYPE_ADVERTISER)
              ->setPlainPassword('12345');

        $userManager->updateUser($advertiser1, false);

        /** @var User $advertiser2 */
        $advertiser2 = $userManager->createUser();
        $advertiser2
              ->setEmail('testadvertiser2@vifeed.ru')
              ->setUsername('testadvertiser2@vifeed.ru')
              ->setBalance(100)
              ->setEnabled(false)
              ->setType(User::TYPE_ADVERTISER)
              ->setPlainPassword('12345');

        $userManager->updateUser($advertiser2, false);

        /** @var User $publisher1 */
        $publisher1 = $userManager->createUser();
        $publisher1
              ->setEmail('testpublisher1@vifeed.ru')
              ->setUsername('testpublisher1@vifeed.ru')
              ->setBalance(100)
              ->setEnabled(true)
              ->setType(User::TYPE_PUBLISHER)
              ->setPlainPassword('12345');

        $userManager->updateUser($publisher1, false);

        /** @var User $publisher2 */
        $publisher2 = $userManager->createUser();
        $publisher2
              ->setEmail('testpublisher2@vifeed.ru')
              ->setUsername('testpublisher2@vifeed.ru')
              ->setBalance(100)
              ->setEnabled(false)
              ->setType(User::TYPE_PUBLISHER)
              ->setPlainPassword('12345');
        $userManager->updateUser($publisher2, false);

        $platform1 = new Platform();
        $platform1->setUser($publisher1)
                  ->setName('name1')
                  ->setDescription('111')
                  ->setUrl('url1')
                  ->setHashId('p1');
        $entityManager->persist($platform1);

        $platform2 = new Platform();
        $platform2->setUser($publisher2)
                  ->setName('name2')
                  ->setDescription('222')
                  ->setHashId('p2')
                  ->setUrl('ya.ru');
        $entityManager->persist($platform2);

        $campaign1 = new Campaign();
        $campaign1->setUser($advertiser1)
                  ->setName('111')
                  ->setBid(1)
                  ->setGeneralBudget(100)
                  ->setBalance(100)
                  ->setDailyBudget(0)
                  ->setStatus(Campaign::STATUS_ON)
                  ->setHashId('c1')
                  ->setHash('123');
        $entityManager->persist($campaign1);

        $campaign2 = new Campaign();
        $campaign2->setUser($advertiser2)
                  ->setName('222')
                  ->setBid(1)
                  ->setGeneralBudget(100)
                  ->setBalance(100)
                  ->setDailyBudget(0)
                  ->setHash('123')
                  ->setHashId('c2')
                  ->setStatus(Campaign::STATUS_ON);
        $entityManager->persist($campaign2);

        $entityManager->flush();

        $fixtures = [
              'advertisers' => [$advertiser1, $advertiser2],
              'publishers'  => [$publisher1, $publisher2],
              'campaigns'   => [$campaign1, $campaign2],
              'platofrms'   => [$platform1, $platform2],
        ];

        return $fixtures;
    }
}
 