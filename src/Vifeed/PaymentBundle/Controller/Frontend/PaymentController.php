<?php

namespace Vifeed\PaymentBundle\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Vifeed\PaymentBundle\Entity\Order;

/**
 * Class PaymentController
 *
 * @package Vifeed\PaymentBundle\Controller|Frontend
 */
class PaymentController extends Controller
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('VifeedPaymentBundle:Frontend:payment.html.twig', []);
    }

    /**
     * Счёт для скачивания
     *
     * @param Order $order
     *
     *
     * @return Response
     */
    public function billAction(Order $order)
    {
        if ($this->getUser() !== $order->getUser()) {
            throw new AccessDeniedHttpException;
        }

        $method = $order->getPaymentInstruction()->getPaymentSystemName();
        if (!in_array($method, ['bank_transfer', 'bank_receipt'])) {
            throw new NotFoundHttpException;
        }

        $billData = $order->getBillData();

        if (!isset($billData['bill']) || !isset($billData['template'])) {
            throw new \Exception('Недостаточно данных о заказе');
        }

        $billGenerator = $this->get('vifeed.payment.bill_generator');
        $billGenerator->setOrderDetails($billData['bill'])
                      ->setTemplate($billData['template']);

        $response = new Response();
        $response->setContent($billGenerator->generate());
        $disposition = $response->headers->makeDisposition(
                                         ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                                         'Счет-' . $order->getId() . '.pdf',
                                         'Bill-' . $order->getId() . '.pdf'
        );
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
