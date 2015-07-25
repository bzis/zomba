<?php

namespace Vifeed\UserBundle\NotificationEvent;

use Vifeed\UserBundle\Entity\User;

/**
 * Class NewCampaignEvent
 * @package Vifeed\UserBundle\NotificationEvent
 */
class NewCampaignEvent extends AbstractNotificationEvent
{
    protected $needsUser = true;
    protected $subject = 'Появилась новая рекламная кампания';
    protected $emailTemplate = 'VifeedUserBundle:Notification:Email/NewCampaign.html.twig';
    protected $smsTemplate =   'VifeedUserBundle:Notification:Sms/NewCampaign.txt.twig';

    /**
     * @return bool
     * @throws \Exception
     */
    public function getSendEmail()
    {
        if (!$this->user instanceof User) {
            throw new \Exception('User не определён');
        }

        return ($this->user->getNotification()['email'] === 1);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function getSendSms()
    {
        if (!$this->user instanceof User) {
            throw new \Exception('User не определён');
        }

        if ($this->user->getNotification()['sms'] !== 1) {
            return false;
        }

        $parameters = $this->getParameters();

        if (isset($parameters['smsMinBudget']) && ($parameters['campaign']['budget'] < $parameters['smsMinBudget'])) {
            return false;
        }

        return true;
    }

}
