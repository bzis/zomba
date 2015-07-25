<?php

namespace Vifeed\PaymentBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use JMS\DiExtraBundle\Annotation as DI;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Vifeed\PaymentBundle\Entity\Wallet;
use Vifeed\PaymentBundle\Form\WalletType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

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
     *     section="Billing API",
     *     input="Vifeed\PaymentBundle\Form\WalletType",
     *     output={
     *          "class"="Vifeed\PaymentBundle\Entity\Wallet",
     *          "groups"={"default"}
     *     },
     *     resource=true,
     *     statusCodes={
     *         201="Returned when successful",
     *         400="Returned when the something was wrong",
     *         403="Returned when the user is not authorized to use this method"
     *     }
     * )
     *
     * @Rest\Put("wallets")
     *
     * @return Response
     */
    public function putWalletAction()
    {
        $form = $this->createForm(new WalletType());
        $form->submit($this->get('request'));

        if ($form->isValid()) {
            /** @var Wallet $wallet */
            $wallet = $form->getData();
            $wallet->setUser($this->getUser());
            $this->em->persist($wallet);
            $this->em->flush();

            $context = new SerializationContext();
            $context->setGroups(array('default'));

            $view = new View($wallet, 201);
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
     *     section="Billing API",
     *     output={
     *          "class"="Vifeed\PaymentBundle\Entity\Wallet",
     *          "groups"={"default"}
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         403="Returned when the user is not authorized to use this method"
     *     }
     * )
     *
     * @return Response
     */
    public function getWalletsAction()
    {
        $data = $this->em->getRepository('VifeedPaymentBundle:Wallet')->getWalletsDataByUser($this->getUser());

        $wallets = [];
        $walletData = [];

        foreach ($data as $row) {
            /** @var Wallet $wallet */
            $wallet = $row[0];
            $wallets[] = $wallet;
            unset($row[0]);
            $walletData[$wallet->getId()] = $row;
        }

        $context = new SerializationContext();
        $context->setGroups(['default'])
                ->setAttribute('wallet_data', $walletData);

        $view = new View($wallets);
        $view->setSerializationContext($context);

        return $this->handleView($view);
    }

    /**
     * Удалить кошелёк
     *
     * @param Wallet $wallet
     *
     * @Rest\Delete("wallets/{id}", requirements={"id"="\d+"})
     * @ParamConverter("wallet", class="VifeedPaymentBundle:Wallet")
     * @ApiDoc(
     *     section="Billing API",
     *     requirements={
     *       {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="id кошелька"}
     *     },
     *     statusCodes={
     *         204="Returned when successful",
     *         403="Returned when the user is not authorized to use this method",
     *         404="Returned when wallet not found"
     *     }
     * )
     *
     * @return Response
     */
    public function deleteWalletAction(Wallet $wallet)
    {
        if ($wallet->getUser() != $this->getUser()) {
            throw new AccessDeniedHttpException('Можно удалять только свои кошельки');
        }

        $this->em->remove($wallet);
        $this->em->flush();

        $view = new View('', 204);

        return $this->handleView($view);
    }

    /**
     * Типы кошельков
     *
     * @ApiDoc(
     *     section="Billing API",
     *     output="array",
     *     statusCodes={
     *         200="Returned when successful",
     *         403="Returned when the user is not authorized to use this method"
     *     }
     * )
     *
     * @return Response
     */
    public function getWalletTypesAction()
    {
        $view = new View(Wallet::getTypes());

        return $this->handleView($view);
    }

}
