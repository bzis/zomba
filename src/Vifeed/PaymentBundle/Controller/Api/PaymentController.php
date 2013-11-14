<?php

namespace Vifeed\PaymentBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use JMS\Payment\CoreBundle\Entity\PaymentInstruction;
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
     * Создать нового юзера
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

                $view = new View($order, 201);
            } else {
                $view = new View($form, 400);
            }
        } else {
            $view = new View($orderForm, 400);
        }

        return $this->handleView($view);
    }

}
