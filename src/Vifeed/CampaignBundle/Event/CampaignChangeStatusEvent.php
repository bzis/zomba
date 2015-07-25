<?php

namespace Vifeed\CampaignBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Vifeed\CampaignBundle\Entity\Campaign;

/**
 * Class CampaignChangeStatus
 * @package Vifeed\CampaignBundle\Event
 */
class CampaignChangeStatusEvent extends Event
{
    /** @var  Campaign */
    private $campaign;

    private $oldStatus;


    /**
     * @param Campaign $campaign
     * @param string   $oldStatus
     */
    public function __construct(Campaign $campaign, $oldStatus)
    {
        $this->campaign = $campaign;
        $this->oldStatus = $oldStatus;
    }

    /**
     * @return Campaign
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @return string
     */
    public function getOldStatus()
    {
        return $this->oldStatus;
    }

    /**
     * @return string
     */
    public function getNewStatus()
    {
        return $this->campaign->getStatus();
    }
} 