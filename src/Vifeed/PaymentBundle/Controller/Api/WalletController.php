<?php

namespace Vifeed\PaymentBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use JMS\DiExtraBundle\Annotation as DI;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Vifeed\PaymentBundle\Entity\Wallet;
use Vifeed\PaymentBundle\Form\WalletType;

/**
 * Class WalletController
 *
 * @package Vifeed\PaymentBundle\Controller
 */
class WalletController extends FOSRestController
{
    /**
     * @DI\Inject("doctrine.orm.entity_manager")
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * Добавить кошелёк
     *
     * @ApiDoc(
     *     section="Frontend API",
     *     input="Vifeed\PaymentBundle\Entity\Wallet",
     *     output="Vifeed\PaymentBundle\Entity\Wallet"
     * )
     *
     * @return Response
     */
    public function putWalletAction()
    {
        $form = $this->createForm(new WalletType());
        $form->submit($this->getRequest());

        if ($form->isValid()) {
            /** @var Wallet $wallet */
            $wallet = $form->getData();
            $wallet->setUser($this->getUser());
            $this->em->persist($wallet);
            $this->em->flush();

            $context = new SerializationContext();
            $context->setGroups(array('default'));

            $view = new View(array('wallet' => $wallet), 201);
            $view->setSerializationContext($context);
        } else {
            $view = new View($form, 400);
        }

        return $this->handleView($view);
    }

    /**
     * Все кошельки юзера
     *
     * @ApiDoc(
     *     section="Frontend API"
     * )
     *
     * @return Response
     */
    public function getWalletsAction()
    {
        /** @var Wallet[] $data */
        $wallets = $this->em->getRepository('VifeedPaymentBundle:Wallet')->findBy(
            array('user' => $this->getUser())
        );

        $data = array(
            'wallets' => $wallets
        );

        $context = new SerializationContext();
        $context->setGroups(array('default'));

        $view = new View($data);
        $view->setSerializationContext($context);

        return $this->handleView($view);
    }

    /**
     * Удалить кошелёк
     *
     * @param int $id
     *
     * @ApiDoc(
     *     section="Frontend API"
     * )
     *
     * @return Response
     */
    public function deleteWalletAction($id)
    {
        /** @var Wallet $wallet */
        $wallet = $this->em->getRepository('VifeedPaymentBundle:Wallet')->find($id);
        if ($wallet === null) {
            throw new NotFoundHttpException('Кошелёк не найден');
        }

        if ($wallet->getUser() != $this->getUser()) {
            throw new \Exception('Можно удалять только свои кошельки');
        }

        $this->em->remove($wallet);
        $this->em->flush();

        $view = new View('', 204);

        return $this->handleView($view);
    }

}
