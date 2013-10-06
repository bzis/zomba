<?php

namespace Vifeed\UserBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
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
     * @ApiDoc(
     *     section="Frontend API"
     * )
     *
     * @return Response
     */
    public function putUserRegisterAction()
    {
        $form = $this->createForm(new RegistrationType());
        $form->submit($this->getRequest());

        if ($form->isValid()) {
            $userManager = $this->container->get('fos_user.user_manager');

            /** @var User $user */
            $user = $form->getData();
            $user->setUsername($user->getEmail())
                  ->setPlainPassword(md5(time() . rand()));
//            ->setEnabled(true);

            $userManager->updateUser($user);

            $view = new View($user, 201);
        } else {
            $view = new View($form, 400);
        }

        return $this->handleView($view);
    }
}
