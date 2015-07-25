<?php

namespace Vifeed\CampaignBundle\Tests\Hashids;

use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\SystemBundle\Tests\TestCase;
use Vifeed\UserBundle\Entity\User;

class HashidsTest extends TestCase
{
    static $entities = [];

    /**
     *
     */
    public function testHashidsSave()
    {
        $userManager = self::getContainer()->get('fos_user.user_manager');

        $advertiser1 = $userManager->createUser();
        $advertiser1
              ->setEmail('testadvertiser1@vifeed.ru')
              ->setUsername('testadvertiser1@vifeed.ru')
              ->setBalance(100)
              ->setEnabled(true)
              ->setType(User::TYPE_ADVERTISER)
              ->setPlainPassword('12345');

        $userManager->updateUser($advertiser1);
        self::$entities[] = $advertiser1;

        $campaign1 = new Campaign();
        $campaign1->setName('111')
                  ->setUser($advertiser1)
                  ->setHash('1111')
                  ->setGeneralBudget(100)
                  ->setDailyBudget(50)
                  ->setBid(1)
                  ->setHashId('aaaaaa');
        self::$em->persist($campaign1);
        self::$em->flush();

        $campaign2 = new Campaign();
        $campaign2->setName('222')
                  ->setDailyBudget(50)
                  ->setUser($advertiser1)
                  ->setHash('2222')
                  ->setBid(1)
                  ->setGeneralBudget(100)
                  ->setHashId('aaaAaa');

        try {
            self::$em->persist($campaign2);
            self::$em->flush();
        } catch (\Exception $e) {
            $this->fail($e->getMessage());

            return;
        }

        $this->assertNotNull($campaign2->getId());
    }

    /**
     * @depends testHashidsSave
     */
    public function testHashidsFind()
    {
        $repo = self::$em->getRepository('VifeedCampaignBundle:Campaign');

        $result1 = $repo->findBy(['hashId' => 'aaaaaa']);
        $this->assertInternalType('array', $result1);
        $this->assertCount(1, $result1);
        $this->assertEquals('111', $result1[0]->getName());

        $result2 = $repo->findBy(['hashId' => 'aaaAaa']);
        $this->assertInternalType('array', $result2);
        $this->assertCount(1, $result2);
        $this->assertEquals('222', $result2[0]->getName());
    }

    public static function tearDownAfterClass()
    {
        self::deleteFixtures(self::$entities);
    }
}
 