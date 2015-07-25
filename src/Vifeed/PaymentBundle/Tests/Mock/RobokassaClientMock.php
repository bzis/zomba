<?php

namespace Vifeed\PaymentBundle\Tests\Mock;

use Karser\RobokassaBundle\Client\Client;

/**
 * Class RobokassaClientMock
 * @package Vifeed\PaymentBundle\Tests\Mock
 */
class RobokassaClientMock extends Client
{
    public function requestOpState($inv_id)
    {
        return 100;
    }
} 