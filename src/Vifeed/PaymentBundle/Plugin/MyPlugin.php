<?php

namespace Vifeed\PaymentBundle\Plugin;

use JMS\Payment\CoreBundle\Plugin\AbstractPlugin;

class MyPlugin extends AbstractPlugin
{

    /**
     * Whether this plugin can process payments for the given payment system.
     *
     * A plugin may support multiple payment systems. In these cases, the requested
     * payment system for a specific transaction  can be determined by looking at
     * the PaymentInstruction which will always be accessible either directly, or
     * indirectly.
     *
     * @param string $paymentSystemName
     *
     * @return boolean
     */
    function processes($paymentSystemName)
    {
        return $paymentSystemName === 'my_payment_type';
    }}
 