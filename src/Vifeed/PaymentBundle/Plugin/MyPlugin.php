<?php

namespace Vifeed\PaymentBundle\Plugin;

use JMS\Payment\CoreBundle\Entity\Payment;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Plugin\AbstractPlugin;
use JMS\Payment\CoreBundle\Plugin\PluginInterface;

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
    public function processes($paymentSystemName)
    {
        return $paymentSystemName === 'my_payment_type';
    }

    /**
     * @param FinancialTransactionInterface $transaction
     * @param bool                          $retry
     */
    public function approveAndDeposit(FinancialTransactionInterface $transaction, $retry)
    {
        $data = $transaction->getExtendedData();
        /** @var Payment $payment */
        $payment = $transaction->getPayment();
        $transaction->setReferenceNumber('some_id');
        $transaction->setProcessedAmount($payment->getTargetAmount());
        $transaction->setResponseCode(PluginInterface::RESPONSE_CODE_SUCCESS);
        $transaction->setReasonCode(PluginInterface::REASON_CODE_SUCCESS);
    }
}
 