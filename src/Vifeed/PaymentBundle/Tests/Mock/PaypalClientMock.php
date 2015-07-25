<?php

namespace Vifeed\PaymentBundle\Tests\Mock;

use JMS\Payment\PaypalBundle\Client\Client;
use JMS\Payment\PaypalBundle\Client\Response;

/**
 * Class PaypalClientMock
 * @package Vifeed\PaymentBundle\Tests\Mock
 */
class PaypalClientMock extends Client
{
    public function requestSetExpressCheckout($amount, $returnUrl, $cancelUrl, array $optionalParameters = array())
    {
        $parameters = [
            'TOKEN' => 'AAA',
            'ACK' => "Success"
        ];

        return new Response($parameters);
    }
} 