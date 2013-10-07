<?php

namespace Vifeed\UserBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Vifeed\UserBundle\Entity\User;
use Vifeed\UserBundle\Form\RegistrationType;

/**
 * Class RegistrationController
 *
 * @package Vifeed\UserBundle\Controller
 */
class RegistrationController extends FOSRestController
{
    /**
     * Создать нового юзера
     *
     * @ApiDoc()
     *
     * @return Response
     */
    public function putUserRegisterAction()
    {
        $form = $this->createForm(new RegistrationType());
        $form->submit($this->getRequest());

        if ($form->isValid()) {
            $userManager = $this->container->get('fos_user.user_manager');
            $generator = new SecureRandom();
            $pass = $generator->nextBytes(6);
            /** @var User $user */
            $user = $form->getData();
            $user->setUsername($user->getEmail())
                  ->setPlainPassword($pass);
//            ->setEnabled(true);

            $userManager->updateUser($user);

            $view = new View($user, 201);
        } else {
            $view = new View($form, 400);
        }

        return $this->handleView($view);
    }
}
