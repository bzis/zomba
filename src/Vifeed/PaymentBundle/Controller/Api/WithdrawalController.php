<?php

namespace Vifeed\PaymentBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use JMS\DiExtraBundle\Annotation as DI;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
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
     *     section="Frontend API",
     *     input="Vifeed\PaymentBundle\Entity\Wallet",
     *     output="Vifeed\PaymentBundle\Entity\Wallet"
     * )
     *
     * @return Response
     * @throws \Exception
     */
    public function putWithdrawalAction()
    {
        $form = $this->createForm(new WithdrawalType());
        $form->submit($this->getRequest());
        if ($form->isValid()) {
            /** @var Withdrawal $withdrawal */
            $withdrawal = $form->getData();

            if ($withdrawal->getWallet()->getUser() != $this->getUser()) {
                throw new \Exception('Можно вывести только на свой кошелёк');
            }
            if ($withdrawal->getAmount() > $this->getUser()->getBalance()) {
                $form->get('amount')->addError(new FormError('Недостаточно денег на балансе'));
            } else {

                $withdrawal
                      ->setUser($this->getUser())
                      ->setStatus(Withdrawal::STATUS_CREATED);
                $this->em->persist($withdrawal);
                $this->getUser()->updateBalance(0 - $withdrawal->getAmount());

                /* todo: здесь дёрнуть апи платёжных систем для вывода денег */

                $this->em->flush();

                $context = new SerializationContext();
                $context->setGroups(array('default'));

                $view = new View(array('withdrawal' => $withdrawal), 201);
                $view->setSerializationContext($context);
            }
        }

        if (!$form->isValid()) {
            $view = new View($form, 400);
        }

        return $this->handleView($view);
    }

}
