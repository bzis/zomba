<?php

namespace Vifeed\CampaignBundle\Consumer;

use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Vifeed\CampaignBundle\Manager\CampaignManager;
use Vifeed\UserBundle\Entity\User;
use Vifeed\UserBundle\Manager\UserNotificationManager;
use Vifeed\UserBundle\NotificationEvent\NewCampaignEvent;

/**
 * Class NewCampaignNotificationConsumer
 *
 * @package Vifeed\CampaignBundle\Consumer
 */
class NewCampaignNotificationConsumer implements ConsumerInterface
{
    private $logger;
    private $em;
    private $notificationManager;
    private $campaignManager;
    private $container;

    /**
     *
     */
    public function __construct(EntityManager $em, UserNotificationManager $notificationManager, LoggerInterface $logger,
          CampaignManager $campaignManager, ContainerInterface $container)
    {
        $this->logger = $logger;
        $this->em = $em;
        $this->notificationManager = $notificationManager;
        $this->campaignManager = $campaignManager;
        $this->container = $container;
    }

    public function execute(AMQPMessage $msg)
    {
        $this->logger->info($msg->body, ['campaign']);

        $campaignRepo = $this->em->getRepository('VifeedCampaignBundle:Campaign');
        $campaign = $campaignRepo->findOneBy(['id' => $msg->body, 'isNew' => true]);

        if (!$campaign) {
            return;
        }

        $userRepo = $this->em->getRepository('VifeedUserBundle:User');
        $users = $userRepo->findPartnersForNotification();

        // нужно для того, чтобы в шаблоне письма подцеплялись ассеты
        $this->container->enterScope('request');
        $this->container->set('request', new Request(), 'request');

        $data = ['campaign' => $this->campaignManager->getCampaignParametersForPartner($campaign)];
        $data['campaign']['id'] = $campaign->getId();
        $data['campaign']['name'] = $campaign->getName();
        $data['campaign']['hash'] = $campaign->getHash();
        $data['smsMinBudget'] = $this->container->getParameter('vifeed.campaign.min_balance_for_sms');

        foreach ($users as $user) {
            $notificationEvent = new NewCampaignEvent($data);
            $this->notificationManager->notify($user, $notificationEvent);
        }

        $campaign->setIsNew(false);
        $this->em->persist($campaign);
        $this->em->flush($campaign);
    }

}