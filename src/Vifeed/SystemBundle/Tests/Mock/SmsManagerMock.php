<?php

namespace Vifeed\SystemBundle\Tests\Mock;

use Vifeed\SystemBundle\Sms\SmsManager;

/**
 * @package Vifeed\SystemBundle\Tests
 */
class SmsManagerMock extends SmsManager
{
    static $messages = [];

    /**
     * @param $message
     * @param $phone
     */
    public function send($message, $phone)
    {
        static::$messages[] = [
              'message' => $message,
              'phone'   => $phone
        ];
    }


} 