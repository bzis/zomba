<?php

namespace Vifeed\SystemBundle\Tests\Mock;

use Swift_Mime_Message;
use Swift_RfcComplianceException;

/**
 * Class Mailer
 * @package Vifeed\SystemBundle\Tests
 */
class MailerMock extends \Swift_Mailer
{
    static $messages = [];

    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        static::$messages[] = $message;
        return parent::send($message, $failedRecipients); 
    }

    /**
     *
     */
    public function reset()
    {
        self::$messages = [];
    }


} 