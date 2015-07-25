<?php

namespace Vifeed\SystemBundle\Sms;

use Symfony\Bridge\Monolog\Logger;
use Vresh\TwilioBundle\Service\TwilioWrapper;

/**
 * Class SmsManager
 *
 * @package Vifeed\UserBundle\Manager
 */
class SmsManager
{
    /** @var \Vresh\TwilioBundle\Service\TwilioWrapper */
    protected $gate;
    protected $senderNumber;
    protected $logger;

    /**
     *
     */
    public function __construct(TwilioWrapper $gate, $senderNumber, Logger $logger)
    {
        $this->gate = $gate;
        $this->senderNumber = $senderNumber;
        $this->logger = $logger;
    }

    /**
     *
     */
    public function send($message, $phone)
    {
        /** @var \Services_Twilio_Rest_Account $account */
        $account = $this->gate->account;
        /** @var \Services_Twilio_Rest_Messages $messages */
        $messages = $account->messages;
        try {
            $messages->sendMessage($this->senderNumber, $phone, $message);
        } catch (\Services_Twilio_RestException $e) {
            $this->logger->warning($e->getMessage(), ['sms']);
        }
    }
} 