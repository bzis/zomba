<?php

namespace Vifeed\SystemBundle\Tests\Mock;

/**
 * Class RabbitMock
 * @package Vifeed\SystemBundle\Tests\Mock
 */
class RabbitMock
{
    static $messages = [];

    /**
     *
     */
    public function publish($message)
    {
        static::$messages[] = $message;
    }
} 