<?php

namespace Vifeed\BankPaymentBundle\Plugin;

use Doctrine\ORM\EntityManager;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Model\PaymentInstructionInterface;
use JMS\Payment\CoreBundle\Plugin\AbstractPlugin;
use JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException;
use JMS\Payment\CoreBundle\Plugin\PluginInterface;
use Symfony\Component\Routing\Router;
use Vifeed\PaymentBundle\Entity\Order;
use Vifeed\BankPaymentBundle\Exception\Action\DownloadFile;

abstract class AbstractBankPlugin extends AbstractPlugin
{
    /** @var \Vifeed\PaymentBundle\Repository\OrderRepository */
    protected $orderRepository;
    protected $router;

    /**
     *
     */
    public function __construct(EntityManager $entityManager, Router $router)
    {
        $this->orderRepository = $entityManager->getRepository('VifeedPaymentBundle:Order');
        $this->router = $router;
    }

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
    abstract public function processes($paymentSystemName);

    /**
     * @param FinancialTransactionInterface $transaction
     *
     * @return array
     */
    abstract protected function getBillData(FinancialTransactionInterface $transaction);


    /**
     * @param FinancialTransactionInterface $transaction
     * @param bool                          $retry
     */
    public function approveAndDeposit(FinancialTransactionInterface $transaction, $retry)
    {
        /** @var PaymentInstructionInterface $instruction */
        $instruction = $transaction->getPayment()->getPaymentInstruction();

        if ($transaction->getState() === FinancialTransactionInterface::STATE_NEW) {
            /** @var Order $order */
            $order = $this->getOrderByPaymentInstruction($instruction);
            $order->setBillData($this->getBillData($transaction));
            throw $this->createRedirectActionException($transaction, $order);
        }

        $transaction->setProcessedAmount($instruction->getAmount());
        $transaction->setResponseCode(PluginInterface::RESPONSE_CODE_SUCCESS);
        $transaction->setReasonCode(PluginInterface::REASON_CODE_SUCCESS);
    }

    /**
     * @param FinancialTransactionInterface      $transaction
     * @param \Vifeed\PaymentBundle\Entity\Order $order
     *
     * @return ActionRequiredException
     */
    protected function createRedirectActionException(FinancialTransactionInterface $transaction, Order $order)
    {

        $actionRequest = new ActionRequiredException('Redirect to pay');
        $actionRequest->setFinancialTransaction($transaction);
        $url = $this->router->generate('order_bill', ['id' => $order->getId()], false);
        $actionRequest->setAction(new DownloadFile($url));

        return $actionRequest;
    }

    /**
     * @param PaymentInstructionInterface $paymentInstruction
     *
     * @return Order
     */
    protected function getOrderByPaymentInstruction(PaymentInstructionInterface $paymentInstruction)
    {
        return $this->orderRepository->findOneBy(['paymentInstruction' => $paymentInstruction]);
    }

}
 