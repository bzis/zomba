<?php

namespace Vifeed\UserBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
     *     section="User API",
     *     resource=true,
     *     input="Vifeed\UserBundle\Form\RegistrationType",
     *     statusCodes={
     *         201="Returned when successful",
     *         400="Returned when the something was wrong"
     *     }
     * )
     *
     * @return Response
     */
    public function putUsersAction()
    {
        $form = $this->createForm(new RegistrationType());
        $form->submit($this->getRequest());

        /** @var User $user */
        $user = $form->getData();

        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->container->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $this->getRequest());
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);
        if ($form->isValid()) {
            $userManager = $this->container->get('fos_user.user_manager');

            $event = new FormEvent($form, $this->getRequest());
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

            $user->setUsername($user->getEmail())
                 ->setEnabled(true);
            $userManager->updateUser($user);

            $wsseToken = $this->container->get('vifeed.user.wsse_token_manager')->createUserToken($user->getId());
            $data = ['token' => $wsseToken];

            $view = new View($data, 201);

            // todo: нужно ли всегда кидать этот эвент? После него пользователь аутентицируется
            $dispatcher->dispatch(
                       FOSUserEvents::REGISTRATION_COMPLETED,
                       new FilterUserResponseEvent($user, $this->getRequest(), new Response())
            );

        } else {
            $view = new View($form, 400);
        }

        return $this->handleView($view);
    }

    /**
     * Подтвердить email
     *
     * @ApiDoc(
     *     section="User API",
     *     parameters={
     *       {"name"="token", "dataType"="string", "required"=true},
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Returned when the something was wrong"
     *     }
     * )
     *
     * @return Response
     */
    public function patchUsersConfirmAction()
    {
        $token = $this->getRequest()->get('token');

        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->container->get('fos_user.user_manager');

        /** @var User $user */
        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new BadRequestHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->container->get('event_dispatcher');

        $user->setConfirmationToken(null)
             ->setEmailConfirmed(true);

        $userManager->updateUser($user);

        $event = new GetResponseUserEvent($user, $this->getRequest());
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRM, $event);

        $response = ($event->getResponse() === null) ? new Response() : $event->getResponse();

        $dispatcher->dispatch(
                   FOSUserEvents::REGISTRATION_CONFIRMED,
                   new FilterUserResponseEvent($user, $this->getRequest(), $response)
        );

        $view = new View('');

        return $this->handleView($view);
    }

}
