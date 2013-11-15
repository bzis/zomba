<?php

namespace Vifeed\PaymentBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use JMS\Payment\CoreBundle\Entity\FinancialTransaction;
use JMS\Payment\CoreBundle\Entity\PaymentInstruction;
use JMS\Payment\CoreBundle\Plugin\Exception\Action\VisitUrl;
use JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException;
use JMS\Payment\CoreBundle\PluginController\Result;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use JMS\DiExtraBundle\Annotation as DI;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use Vifeed\PaymentBundle\Entity\Order;
use Vifeed\PaymentBundle\Form\OrderType;


/**
 * Class PaymentController
 *
 * @package Vifeed\PaymentBundle\Controller
 */
class PaymentController extends FOSRestController
{
    /**
     * @DI\Inject("doctrine.orm.entity_manager")
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @DI\Inject("payment.plugin_controller")
     * @var \JMS\Payment\CoreBundle\PluginController\EntityPluginController
     */
    private $ppc;


    /**
     * Создать счёт на пополнение баланса
     *
     * @ApiDoc(
     *     section="Frontend API"
     * )
     *
     * @return Response
     */
    public function putOrderAction()
    {
        $orderForm = $this->createForm(new OrderType());
        $orderForm->submit($this->getRequest());
        if ($orderForm->isValid()) {
            $order = $orderForm->getData();
            $form = $this->container->get('form.factory')->create(
                'jms_choose_payment_method',
                null,
                array(
                     'csrf_protection'   => false,
                     'amount'         => $order->getAmount(),
                     'currency'       => 'RUR',
//                     'default_method' => 'payment_paypal', // Optional
                )
            );
            $form->submit($this->getRequest());
            if ($form->isValid()) {
                /** @var PaymentInstruction $instruction */
                $instruction = $form->getData();
                $this->ppc->createPaymentInstruction($instruction);
                $order->setUser($this->getUser())
                      ->setPaymentInstruction($instruction);
                $this->em->persist($order);
                $this->em->flush($order);

                // todo: что здесь должно происходить?
                $view = new View($order, 201);
            } else {
                $view = new View($form, 400);
            }
        } else {
            $view = new View($orderForm, 400);
        }

        return $this->handleView($view);
    }

    /**
     * Платёж совершён
     *
     * todo: возможно, реальные платёжные системы могут редиректить сюда методами GET и POST
     *
     * @param \Vifeed\PaymentBundle\Entity\Order $order
     *
     * @ApiDoc(
     *     section="Frontend API"
     * )
     *
     * @throws \RuntimeException
     * @throws \JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException
     *
     * @return Response
     */
    public function getOrderCompleteAction(Order $order)
    {
        $instruction = $order->getPaymentInstruction();
        /** @var FinancialTransaction $pendingTransaction */
        $pendingTransaction = $instruction->getPendingTransaction();

        if (null === $pendingTransaction) {
            $payment = $this->ppc->createPayment(
                $instruction->getId(),
                $instruction->getAmount() - $instruction->getDepositedAmount()
            );
        } else {
            $payment = $pendingTransaction->getPayment();
        }

        $result = $this->ppc->approveAndDeposit($payment->getId(), $payment->getTargetAmount());

        if (Result::STATUS_PENDING === $result->getStatus()) {
            $ex = $result->getPluginException();

            if ($ex instanceof ActionRequiredException) {
                $action = $ex->getAction();

                if ($action instanceof VisitUrl) {
                    return $this->handleView(new View($action->getUrl(), 303));
                }

                throw $ex;
            }
        } else {
            if (Result::STATUS_SUCCESS !== $result->getStatus()) {
                throw new \RuntimeException('Transaction was not successful: ' . $result->getReasonCode());
            }
        }

        $amount = $result->getPaymentInstruction()->getApprovedAmount();

        $this->getUser()->updateBalance($amount);
        $this->container->get('fos_user.user_manager')->updateUser($this->getUser());

        $view = new View(array(
                              'amount' => $amount
                         ), 200);

        return $this->handleView($view);
    }

}
