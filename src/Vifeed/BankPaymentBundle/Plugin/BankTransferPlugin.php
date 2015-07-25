<?php

namespace Vifeed\BankPaymentBundle\Plugin;

use JMS\Payment\CoreBundle\Entity\PaymentInstruction;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;

class BankTransferPlugin extends AbstractBankPlugin
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
        return $paymentSystemName === 'bank_transfer';
    }


    /**
     * @param FinancialTransactionInterface $transaction
     *
     * @return array
     */
    protected function getBillData(FinancialTransactionInterface $transaction)
    {
        /** @var PaymentInstruction $paymentInstruction */
        $paymentInstruction = $transaction->getPayment()->getPaymentInstruction();
        $order = $this->getOrderByPaymentInstruction($paymentInstruction);

        $data = [
              'bill'     => [
                    'order_id'            => $order->getId(),
                    'sum'                 => $paymentInstruction->getAmount(),
                    'client_company_name' => $order->getUser()->getCompany()->getName()
              ],
              'template' => 'transfer'
        ];

        return $data;
    }
}
 