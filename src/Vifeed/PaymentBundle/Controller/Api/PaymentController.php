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
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Vifeed\BankPaymentBundle\Exception\Action\DownloadFile;
use Vifeed\PaymentBundle\Entity\Order;
use Vifeed\PaymentBundle\Form\OrderType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Vifeed\UserBundle\Entity\User;


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
     *     section="Billing API",
     *     input="Vifeed\PaymentBundle\Form\OrderType",
     *     resource=true,
     *     statusCodes={
     *         201="Returned when successful",
     *         400="Returned when something is wrong",
     *         403="Returned when the user is not authorized to use this method"
     *     }
     * )
     *
     * @Rest\Put("orders")
     *
     * @return Response
     */
    public function putOrderAction()
    {
        if ($this->getUser()->getType() !== User::TYPE_ADVERTISER) {
            throw new AccessDeniedHttpException;
        }
        $request = $this->get('request');
        $orderForm = $this->createForm(new OrderType());
        $orderForm->submit($request);
        if ($orderForm->isValid()) {
            /** @var Order $order */
            $order = $orderForm->getData();

            $method = $request->get('jms_choose_payment_method')['method'];

            if ($method == 'bank_transfer') {
                if (!$this->getUser()->getCompany()) {
                    throw new BadRequestHttpException('Вы не ввели реквизиты компании');
                }
                if (!$this->getUser()->getCompany()->isApproved()) {
                    throw new BadRequestHttpException('Ваша компания проверяется');
                }
            }

            if ($method == 'paypal_express_checkout') {
                $currency = 'RUB';
            } else {
                $currency = 'RUR';
            }

            $form = $this->container->get('form.factory')->create(
                                    'jms_choose_payment_method',
                                    null,
                                    [
                                          'csrf_protection' => false,
                                          'amount'          => $order->getAmount(),
                                          'currency'        => $currency,
                                          //                     'default_method' => 'payment_paypal', // Optional
                                          'predefined_data' => [
                                                'qiwi_wallet'             => [
                                                      'return_url' => $this->get('router')->generate('payment_completed', [], true),
                                                      'comment'    => 'Оплата заказа №' . $order->getId(),
                                                      'lifetime'   => new \DateTime('+14 day'),
                                                      'alarm'      => true,
                                                      'create'     => true
                                                ],
                                                'paypal_express_checkout' => [
                                                      'return_url' => $this->get('router')->generate('payment_completed', [], true),
                                                      'cancel_url' => $this->get('router')->generate('payment_declined', [], true),
                                                ],
                                          ]
                                    ]
            );

            $form->submit($request);
            if ($form->isValid()) {
                /** @var PaymentInstruction $instruction */
                $instruction = $form->getData();
                $this->ppc->createPaymentInstruction($instruction);
                $order->setUser($this->getUser())
                      ->setPaymentInstruction($instruction);
                $this->em->persist($order);
                $this->em->flush($order);

                return $this->getOrderCompleteAction($order);

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
     * @param Order $order
     *
     * @Rest\Get("orders/{id}/complete", requirements={"id"="\d+"})
     * @ParamConverter("order", class="VifeedPaymentBundle:Order")
     * @ApiDoc(
     *     section="Billing API",
     *     requirements={
     *         {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="id заказа"}
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         303="Returned when some user action is required by payment system",
     *         403="Returned when the user is not authorized to use this method",
     *         404="Returned when order is not found"
     *     }
     * )
     *
     * @throws \RuntimeException
     * @throws \JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException
     *
     * @return Response
     */
    public function getOrderCompleteAction(Order $order)
    {
        if ($this->getUser() !== $order->getUser()) {
            throw new AccessDeniedHttpException;
        }
        $instruction = $order->getPaymentInstruction();
        // если заказ уже оплачен, то нам тут нечего ловить
        if ($instruction->getAmount() != $instruction->getApprovedAmount()) {

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

            /** @var Result $result */
            $result = $this->ppc->approveAndDeposit($payment->getId(), $payment->getTargetAmount());

            if (Result::STATUS_PENDING === $result->getStatus()) {
                $ex = $result->getPluginException();

                $order->setStatus(Order::STATUS_PENDING);
                $this->em->persist($order);
                $this->em->flush($order);

                if ($ex instanceof ActionRequiredException) {
                    $action = $ex->getAction();

                    if ($action instanceof DownloadFile) {
                        return $this->handleView(new View(['url' => $action->getUrl()], 303));

                    } elseif ($action instanceof VisitUrl) {
                        $data = ['url'     => $action->getUrl(),
                                 'orderId' => $order->getId()];

                        return $this->handleView(new View($data, 303));
                    }

                    throw $ex;
                }
            }
        }
        $view = new View(['status' => $order->getStatus()]);

        return $this->handleView($view);
    }

}
