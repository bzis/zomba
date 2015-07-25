<?php

namespace Vifeed\UserBundle\NotificationEvent;

/**
 * Class RegistrationConfirmationEvent
 * @package Vifeed\UserBundle\NotificationEvent
 */
class EmailConfirmationEvent extends AbstractNotificationEvent
{
    protected $sendEmail = true;
    protected $subject = 'Подтверждение email';
    protected $emailTemplate = 'VifeedUserBundle:Notification/Email:Registration.html.twig';

}
