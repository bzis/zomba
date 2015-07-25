<?php

namespace Vifeed\PlatformBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Vifeed\PlatformBundle\Entity\Platform;
use Vifeed\PlatformBundle\Entity\VkPlatform;
use Vifeed\SystemBundle\Tests\ApiTestCase;
use Vifeed\UserBundle\Entity\User;

/**
 * Class PlatformControllerTest
 *
 * @package Vifeed\PlatformBundle\Tests\Controller
 */
class PlatformControllerTestCase extends ApiTestCase
{
    /**
     * @return array
     */
    protected static function loadTestFixtures()
    {
        $userManager = self::getContainer()->get('fos_user.user_manager');
        /** @var EntityManager $entityManager */
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');

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
              ->setEnabled(true)
              ->setType(User::TYPE_PUBLISHER)
              ->setPlainPassword('12345');
        $userManager->updateUser($publisher2, false);

        $platform1 = new Platform();
        $platform1->setUser($publisher1)
                  ->setName('name1')
                  ->setDescription('111')
                  ->setUrl('url1');
        $entityManager->persist($platform1);

        $platform2 = new Platform();
        $platform2->setUser($publisher2)
                  ->setName('name2')
                  ->setDescription('222')
                  ->setUrl('ya.ru');
        $entityManager->persist($platform2);

        $platform3 = new Platform();
        $platform3->setUser($publisher2)
                  ->setName('name3')
                  ->setDescription('333')
                  ->setUrl('ya2.ru');
        $entityManager->persist($platform3);

        $platform4 = new VkPlatform();
        $platform4->setUser($publisher2)
                  ->setName('name3')
                  ->setDescription('333')
                  ->setUrl('vk.com/12377')
                  ->setVkId(12377);
        $entityManager->persist($platform4);

        // удалённая площадка (soft-delete)
        $platform5 = new VkPlatform();
        $platform5->setUser($publisher2)
                  ->setName('name4')
                  ->setDescription('333')
                  ->setUrl('vk.com/12379')
                  ->setVkId(12379)
                  ->setDeletedAt(new \DateTime('2014-02-10'));
        $entityManager->persist($platform5);

        $entityManager->flush();

        $tokenManager->createUserToken($advertiser->getId());
        $tokenManager->createUserToken($publisher1->getId());
        $tokenManager->createUserToken($publisher2->getId());

        return array(
              'advertiser' => $advertiser,
              'publishers' => [$publisher1, $publisher2],
              'platforms'  => [$platform1, $platform2, $platform3, $platform4, $platform5],
        );
    }

}
