<?php

namespace Vifeed\PaymentBundle\Controller\Api;

use Doctrine\DBAL\LockMode;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use JMS\DiExtraBundle\Annotation as DI;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Vifeed\PaymentBundle\Entity\Withdrawal;
use Vifeed\PaymentBundle\Form\WithdrawalType;

/**
 * Class WithdrawalController
 *
 * @package Vifeed\PaymentBundle\Controller
 */
class WithdrawalController extends FOSRestController
{
    /**
     * @DI\Inject("doctrine.orm.entity_manager")
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * вывести деньги
     *
     * @ApiDoc(
     *     section="Billing API",
     *     input="Vifeed\PaymentBundle\Form\WithdrawalType",
     *     output={
     *          "class"="Vifeed\PaymentBundle\Entity\Withdrawal",
     *          "groups"={"default"}
     *     },
     *     resource=true,
     *     statusCodes={
     *         201="Returned when successful",
     *         400="Returned when something is wrong",
     *         403="Returned when the user is not authorized to use this method"
     *     }
     * )
     *
     * @return Response
     */
    public function putWithdrawalAction()
    {
        $form = $this->createForm(new WithdrawalType());
        $form->submit($this->get('request'));
        if ($form->isValid()) {
            /** @var Withdrawal $withdrawal */
            $withdrawal = $form->getData();

            if ($withdrawal->getWallet()->getUser() != $this->getUser()) {
                throw new AccessDeniedHttpException('Можно вывести только на свой кошелёк');
            }

            if ($withdrawal->getAmount() > $this->getUser()->getBalance()) {
                $form->get('amount')->addError(new FormError('Недостаточно денег на балансе'));
            } else {
                $userRepo = $this->em->getRepository('VifeedUserBundle:User');

                $this->em->beginTransaction();
                $this->em->lock($this->getUser(), LockMode::PESSIMISTIC_WRITE);
                try {
                    $withdrawal
                          ->setUser($this->getUser())
                          ->setStatus(Withdrawal::STATUS_CREATED);
                    $this->em->persist($withdrawal);
                    $userRepo->updateBalance($this->getUser(), - $withdrawal->getAmount());
                    $this->em->flush();
                    $this->em->commit();
                } catch (\Exception $e) {
                    $this->em->rollback();
                    $this->em->close();
                    throw $e;
                }

                $mailer = $this->get('vifeed.mailer');
                $message = $mailer->renderMessage('VifeedPaymentBundle:Email:withdrawal.html.twig', ['withdrawal' => $withdrawal]);
                $mailer->sendMessage('Запрос на вывод средств', $message, $this->container->getParameter('withdrawal.notification.email'));

                $context = new SerializationContext();
                $context->setGroups(array('default'));

                $view = new View($withdrawal, 201);
                $view->setSerializationContext($context);
            }
        }

        if (!$form->isValid()) {
            $view = new View($form, 400);
        }

        return $this->handleView($view);
    }

}
