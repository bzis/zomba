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
    public function putUserRegisterAction()
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

            $view = new View($user, 201);

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
     *
     * @param \Vifeed\UserBundle\Entity\User $user
     *
     * @return User
     */
    private function prepareUserForRegistration(User $user)
    {

    }
}
