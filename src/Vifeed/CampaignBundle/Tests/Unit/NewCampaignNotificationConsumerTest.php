<?php
namespace Vifeed\CampaignBundle\Tests\Unit;

use PhpAmqpLib\Message\AMQPMessage;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\SystemBundle\Tests\ApiTestCase;
use Vifeed\UserBundle\Entity\User;

class NewCampaignNotificationConsumerTest extends ApiTestCase
{

    /**
     * нотификация пользователей о новой кампании. Бюджет низкий, смс не отправляется
     */
    public function testNewCampaignNotificationConsumerLowBudget()
    {
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][0];
        $this->assertEquals(true, $campaign->isNew());

        $message = new AMQPMessage($campaign->getId());
        $consumer = $this->getContainer()->get('vifeed.rabbit.new_campaign_notify.consumer');
        $consumer->execute($message);

        $mailCollector = $this->getContainer()->get('mailer');
        $this->assertCount(1, $mailCollector::$messages);
        $collectedMessages = $mailCollector::$messages;

        /** @var \Swift_Message $message */
        $message = $collectedMessages[0];
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertContains($campaign->getName(), $message->getBody());
        $this->assertContains('42 руб.', $message->getBody());
        $this->assertContains('2.1 руб.', $message->getBody());

        $smsCollector = $this->getContainer()->get('vifeed.sms_manager');
        $this->assertCount(0, $smsCollector::$messages);

        $campaign = $this->getEntityManager()->find('VifeedCampaignBundle:Campaign', $campaign->getId());
        $this->assertEquals(false, $campaign->isNew());

    }

    /**
     * нотификация пользователей о новой кампании. Бюджет высокий, смс отправляется
     */
    public function testNewCampaignNotificationConsumerHigherBudget()
    {
        $mailCollector = $this->getContainer()->get('mailer');
        $mailCollector->reset();

        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaigns'][1];
        $this->assertEquals(true, $campaign->isNew());

        $message = new AMQPMessage($campaign->getId());
        $consumer = $this->getContainer()->get('vifeed.rabbit.new_campaign_notify.consumer');
        $consumer->execute($message);

        $this->assertCount(1, $mailCollector::$messages);
        $collectedMessages = $mailCollector::$messages;

        /** @var \Swift_Message $message */
        $message = $collectedMessages[0];
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertContains($campaign->getName(), $message->getBody());
        $this->assertContains('350 руб.', $message->getBody());
        $this->assertContains('2.1 руб.', $message->getBody());

        $smsCollector = $this->getContainer()->get('vifeed.sms_manager');
        $this->assertCount(1, $smsCollector::$messages);
        $collectedMessages = $smsCollector::$messages;
        $message = $collectedMessages[0];
        $this->assertContains($campaign->getName(), $message['message']);
        $this->assertContains('350 руб.', $message['message']);
        $this->assertContains('2.1 руб.', $message['message']);

        $campaign = $this->getEntityManager()->find('VifeedCampaignBundle:Campaign', $campaign->getId());
        $this->assertEquals(false, $campaign->isNew());

    }

    protected static function loadTestFixtures()
    {
        $fixtures = [];

        $userManager = self::getContainer()->get('fos_user.user_manager');
        $campaignManager = self::getContainer()->get('vifeed.campaign.manager');

        /** @var User $publisher */
        $publisher = $userManager->createUser();
        $publisher
              ->setEmail('testpublisher2@vifeed.ru')
              ->setUsername('testpublisher2@vifeed.ru')
              ->setBalance(100)
              ->setEnabled(true)
              ->setType(User::TYPE_PUBLISHER)
              ->setPlainPassword('12345')
              ->setPhone(1234567);
        $userManager->updateUser($publisher);
        $publisher->setNotification(['sms' => true]);
        $userManager->updateUser($publisher);

        /** @var User $advertiser */
        $advertiser = $userManager->createUser();
        $advertiser
              ->setEmail('testadvertiser1@vifeed.ru')
              ->setUsername('testadvertiser1@vifeed.ru')
              ->setBalance(100)
              ->setEnabled(true)
              ->setType(User::TYPE_ADVERTISER)
              ->setPlainPassword('12345');
        $userManager->updateUser($advertiser);

        $campaign0 = new Campaign();
        $campaign0
              ->setStatus(Campaign::STATUS_ON)
              ->setBid(3)
              ->setName('000campaign')
              ->setHash(substr(md5(mt_rand(1, 100)), 0, 11))
              ->setUser($advertiser)
              ->setDailyBudget(10)
              ->setGeneralBudget(60);
        $campaignManager->save($campaign0);

        $campaign1 = new Campaign();
        $campaign1
              ->setStatus(Campaign::STATUS_ON)
              ->setBid(3)
              ->setName('111campaign')
              ->setHash(substr(md5(mt_rand(1, 100)), 0, 11))
              ->setUser($advertiser)
              ->setDailyBudget(0)
              ->setGeneralBudget(500);
        $campaignManager->save($campaign1);

        $fixtures = [
              'publisher'  => $publisher,
              'advertiser' => $advertiser,
              'campaigns'  => [$campaign0, $campaign1]
        ];

        return $fixtures;

    }
}
 