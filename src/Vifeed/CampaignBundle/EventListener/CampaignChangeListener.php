<?php

namespace Vifeed\CampaignBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Component\DependencyInjection\Container;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\SystemBundle\RabbitMQ\ConnectionManager;

/**
 * Class CampaignChangeListener
 * @package Vifeed\CampaignBundle\EventListener
 */
class CampaignChangeListener
{
    /** @var \OldSound\RabbitMqBundle\RabbitMq\Producer */
    private $rabbitProducer;

    /**
     *
     */
    public function __construct($rabbitProducer = null)
    {
        $this->rabbitProducer = $rabbitProducer;
    }

    /**
     * @param Campaign $campaign
     * @param          $event
     */
    public function preFlush(Campaign $campaign, $event)
    {
        if ($campaign->getHashId() && $campaign->getStatus() !== $campaign->getOldStatus()) {
            if ($campaign->isNew() && $campaign->getStatus() == Campaign::STATUS_ON) {
//                $campaign->setIsNew(false);
                $this->rabbitProducer->publish($campaign->getId());
            }
        }
    }
} 