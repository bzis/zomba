<?php

namespace Vifeed\UserBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Vifeed\UserBundle\Entity\User;
use Vifeed\UserBundle\Form\AdvertiserRegistrationType;
use Vifeed\UserBundle\Form\PublisherRegistrationType;

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
    public function putUsersAction()
    {
        if (is_array($this->getRequest()->get('publisher_registration'))) {
            $type = User::TYPE_PUBLISHER;
            $form = $this->createForm(new PublisherRegistrationType());
        } elseif (is_array($this->getRequest()->get('advertiser_registration'))) {
            $type = User::TYPE_ADVERTISER;
            $form = $this->createForm(new AdvertiserRegistrationType());
        } else {
            throw new BadRequestHttpException('Incorrect data');
        }

        $form->submit($this->getRequest());

        $request = $this->getRequest();

        /** @var User $user */
        $user = $form->getData();
        $user->setType($type);

        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->container->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);
        if ($form->isValid()) {
            $userManager = $this->container->get('fos_user.user_manager');

            $user->prepareForRegistration();

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);
            $userManager->updateUser($user);

            $data = array(
                'success' => true,
            );
            if ($user->isEnabled()) {
                $wsseToken = $this->container->get('vifeed.user.wsse_token_manager')->createUserToken($user->getId());
                $data['token'] = $wsseToken;

            }
            $view = new View($data, 201);

            // todo: нужно ли всегда кидать этот эвент? После него пользователь аутентицируется
            $dispatcher->dispatch(
                FOSUserEvents::REGISTRATION_COMPLETED,
                new FilterUserResponseEvent($user, $request, new Response())
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
     *     section="Frontend API"
     * )
     *
     * @return Response
     */
    public function patchUsersConfirmAction()
    {
        $token = $this->getRequest()->get('token');

        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->container->get('fos_user.user_manager');

        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->container->get('event_dispatcher');

        $user->setConfirmationToken(null);
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $this->getRequest());
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRM, $event);

        $userManager->updateUser($user);

        $response = ($event->getResponse() === null) ? new Response() : $event->getResponse();

        $dispatcher->dispatch(
            FOSUserEvents::REGISTRATION_CONFIRMED,
            new FilterUserResponseEvent($user, $this->getRequest(), $response)
        );

        $view = new View('', 200);

        return $this->handleView($view);
    }

}
