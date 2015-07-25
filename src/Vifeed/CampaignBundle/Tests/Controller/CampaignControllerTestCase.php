<?php

namespace Vifeed\CampaignBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\GeoBundle\Entity\Country;
use Vifeed\SystemBundle\Tests\ApiTestCase;
use Vifeed\UserBundle\Entity\User;

/**
 * Class CampaignControllerTest
 *
 * @package Vifeed\CampaignBundle\Tests\Controller
 */
class CampaignControllerTestCase extends ApiTestCase
{

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

        $country1 = new Country();
        $country1->setName('Россия');
        $entityManager->persist($country1);

        $country2 = new Country();
        $country2->setName('Белоруссия');
        $entityManager->persist($country2);

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
              ->setBalance(200)
              ->setEnabled(true)
              ->setType(User::TYPE_ADVERTISER)
              ->setPlainPassword('12345');

        $userManager->updateUser($advertiser2, false);

        /** @var User $advertiser3 */
        $advertiser3 = $userManager->createUser();
        $advertiser3
              ->setEmail('testadvertiser3@vifeed.ru')
              ->setUsername('testadvertiser3@vifeed.ru')
              ->setBalance(100)
              ->setEnabled(true)
              ->setType(User::TYPE_ADVERTISER)
              ->setPlainPassword('12345');

        $userManager->updateUser($advertiser3, false);

        /** @var User $advertiser4 */
        $advertiser4 = $userManager->createUser();
        $advertiser4
              ->setEmail('testadvertiser4@vifeed.ru')
              ->setUsername('testadvertiser4@vifeed.ru')
              ->setBalance(1)
              ->setEnabled(true)
              ->setType(User::TYPE_ADVERTISER)
              ->setPlainPassword('12345');

        $userManager->updateUser($advertiser4, false);

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

        $campaign1 = new Campaign();
        $campaign1
              ->setStatus(Campaign::STATUS_ON)
              ->setBid(3)
              ->setName('111')
              ->setUser($advertiser1)
              ->setHash(substr(md5(mt_rand(1, 100)), 0, 11))
              ->setDailyBudget(7)
              ->setStartAt(new \DateTime('-2 day'))
              ->setBalance(10)
              ->setGeneralBudget(10);
        $campaignManager->save($campaign1);

        $campaign2 = new Campaign();
        $campaign2
              ->setStatus(Campaign::STATUS_AWAITING)
              ->setBid(3)
              ->setName('222')
              ->setUser($advertiser2)
              ->setHash(substr(md5(mt_rand(1, 100)), 0, 11))
              ->setDailyBudget(0)
              ->setBalance(10)
              ->setGeneralBudget(10);
        $campaignManager->save($campaign2);

        $campaign3 = new Campaign();
        $campaign3
              ->setStatus(Campaign::STATUS_ON)
              ->setBid(3)
              ->setName('333')
              ->setHash(substr(md5(mt_rand(1, 100)), 0, 11))
              ->setUser($advertiser2)
              ->setDailyBudget(10)
              ->setStartAt(new \DateTime())
              ->setBalance(10)
              ->setGeneralBudget(10);
        $campaignManager->save($campaign3);

        $campaign4 = new Campaign();
        $campaign4
              ->setStatus(Campaign::STATUS_PAUSED)
              ->setBid(3)
              ->setBalance(91)
              ->setName('444')
              ->setHash(substr(md5(mt_rand(1, 100)), 0, 11))
              ->setUser($advertiser2)
              ->setDailyBudget(10)
              ->setGeneralBudget(100)
              ->updateDailyBudgetUsed(9);
        $campaignManager->save($campaign4);

        $campaign5 = new Campaign();
        $campaign5
              ->setStatus(Campaign::STATUS_AWAITING)
              ->setBid(3)
              ->setName('555')
              ->setHash(substr(md5(mt_rand(1, 100)), 0, 11))
              ->setUser($advertiser2)
              ->setDailyBudget(10)
              ->setGeneralBudget(4);
        $campaignManager->save($campaign5);

        $campaign6 = new Campaign();
        $campaign6
              ->setStatus(Campaign::STATUS_ARCHIVED)
              ->setBid(3)
              ->setName('666campaign')
              ->setHash(substr(md5(mt_rand(1, 100)), 0, 11))
              ->setUser($advertiser2)
              ->setDailyBudget(10)
              ->setGeneralBudget(60);
        $campaignManager->save($campaign6);

        // удалённая кампания (soft-delete)
        $campaign7 = new Campaign();
        $campaign7
              ->setStatus(Campaign::STATUS_ON)
              ->setBid(3)
              ->setName('777')
              ->setHash('0123456789a')
              ->setUser($advertiser2)
              ->setDailyBudget(10)
              ->setGeneralBudget(0)
              ->setDeletedAt(new \DateTime('2014-02-10'));
        $campaignManager->save($campaign7);

        $campaign8 = new Campaign();
        $campaign8
              ->setStatus(Campaign::STATUS_AWAITING)
              ->setBid(3)
              ->setName('888')
              ->setHash(substr(md5(mt_rand(1, 100)), 0, 11))
              ->setUser($advertiser3)
              ->setDailyBudget(0)
              ->setGeneralBudget(15)
              ->updateDailyBudgetUsed(15)
              ->setBalance(10);
        $campaignManager->save($campaign8);

        $campaign9 = new Campaign();
        $campaign9
              ->setStatus(Campaign::STATUS_ENDED)
              ->setBid(3)
              ->setName('999')
              ->setHash(substr(md5(mt_rand(1, 100)), 0, 11))
              ->setUser($advertiser4)
              ->setDailyBudget(0)
              ->setGeneralBudget(15)
              ->updateDailyBudgetUsed(15);
        $campaignManager->save($campaign9);

        $entityManager->flush();

        $tokenManager->createUserToken($advertiser1->getId());
        $tokenManager->createUserToken($advertiser2->getId());
        $tokenManager->createUserToken($advertiser3->getId());
        $tokenManager->createUserToken($advertiser4->getId());
        $tokenManager->createUserToken($publisher->getId());

        return array(
              'advertisers' => [$advertiser1, $advertiser2, $advertiser3, $advertiser4],
              'publisher'   => $publisher,
              'campaigns'   => [$campaign1, $campaign2, $campaign3, $campaign4, $campaign5, $campaign6, $campaign7, $campaign8, $campaign9],
              'countries'   => [$country1, $country2]
        );
    }
}
 