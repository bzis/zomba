<?php

namespace Vifeed\UserBundle\Manager;

use Psr\Log\LoggerInterface;
use Vifeed\SystemBundle\Mailer\VifeedMailer;
use Vifeed\SystemBundle\Sms\SmsManager;
use Vifeed\UserBundle\Entity\User;
use Vifeed\UserBundle\NotificationEvent\AbstractNotificationEvent;

/**
 * Class UserNotificationManager
 *
 * @package Vifeed\UserBundle\Manager
 */
class UserNotificationManager
{
    protected $mailer;
    protected $smsManager;
    protected $logger;

    /**
     *
     */
    public function __construct(VifeedMailer $mailer, SmsManager $smsManager, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->smsManager = $smsManager;
        $this->logger = $logger;
    }

    /**
     * @param User                      $user
     * @param AbstractNotificationEvent $event
     * @param array                     $parameters
     */
    public function notify(User $user, AbstractNotificationEvent $event, $parameters = [])
    {
        if ($event->needsUser()) {
            $event->setUser($user);
        }

        if (!$parameters) {
            $parameters = $event->getParameters();
        }

        if ($event->getSendEmail()) {
            if (!isset($parameters['subject'])) {
                $parameters['subject'] = $event->getSubject();
            }
            $message = $this->mailer->renderMessage($event->getEmailTemplate(), $parameters);
            try {
                $this->mailer->sendMessage($event->getSubject(), $message, $user->getEmail());
            } catch (\Exception $e) {
                $this->logger->warning('Ошибка при отправке email-сообщения: ' . $e->getMessage());
            }
        }

        if ($event->getSendSms()) {
            $message = $this->mailer->renderMessage($event->getSmsTemplate(), $parameters);
            try {
                $this->smsManager->send($message, $user->getPhone());
            } catch (\Exception $e) {
                $this->logger->warning('Ошибка при отправке sms-сообщения: ' . $e->getMessage());
            }
        }

    }
}
