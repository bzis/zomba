<?php

namespace Vifeed\UserBundle\NotificationEvent;

use Vifeed\UserBundle\Entity\User;

/**
 * Class AbstractNotificationEvent
 *
 * @package Vifeed\UserBundle\NotificationEvent
 */
abstract class AbstractNotificationEvent
{
    protected $sendEmail = false;
    protected $sendSms = false;
    protected $subject = '';
    protected $emailTemplate;
    protected $smsTemplate;
    protected $needsUser = false;
    /** @var User */
    protected $user;
    protected $parameters;

    /**
     * @param array $parameters
     */
    public function __construct($parameters = [])
    {
        $this->setParameters($parameters);
    }

    /**
     * @return mixed
     */
    public function getEmailTemplate()
    {
        return $this->emailTemplate;
    }

    /**
     * @return boolean
     */
    public function getSendEmail()
    {
        return $this->sendEmail;
    }

    /**
     * @return boolean
     */
    public function getSendSms()
    {
        return $this->sendSms;
    }

    /**
     * @return mixed
     */
    public function getSmsTemplate()
    {
        return $this->smsTemplate;
    }

    /**
     * @return bool
     */
    final public function needsUser()
    {
        return $this->needsUser;
    }

    /**
     * @param $user User
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }



}