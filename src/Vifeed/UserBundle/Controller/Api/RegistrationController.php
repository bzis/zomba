<?php

namespace Vifeed\UserBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
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

        $request = $this->getRequest();

        /** @var User $user */
        $user = $form->getData();

        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->container->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if ($form->isValid()) {
            $userManager = $this->container->get('fos_user.user_manager');
            $generator = new SecureRandom();
            $pass = $generator->nextBytes(6);

            $user->setUsername($user->getEmail())
                  ->setPlainPassword($pass);
//            ->setEnabled(true);
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);
            $userManager->updateUser($user);

            $view = new View($user, 201);

            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, new Response()));

        } else {
            $view = new View($form, 400);
        }

        return $this->handleView($view);
    }
}
