<?php

namespace Vifeed\UserBundle\NotificationEvent;

/**
 * Class OrderPaidEvent
 * @package Vifeed\UserBundle\NotificationEvent
 */
class OrderPaidEvent extends AbstractNotificationEvent
{
    protected $sendEmail = true;
    protected $subject = 'Баланс пополнен';
    protected $emailTemplate = 'VifeedUserBundle:Notification:Email/OrderPaid.html.twig';

}
