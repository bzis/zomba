<?php

namespace Vifeed\UserBundle\Controller\Api;

use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use FOS\UserBundle\Doctrine\UserManager;
use FOS\UserBundle\Event\FormEvent;
use JMS\Serializer\SerializationContext;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Vifeed\UserBundle\Entity\Company;
use Vifeed\UserBundle\Entity\User;
use Vifeed\UserBundle\Form\ChangePasswordFormType;
use Vifeed\UserBundle\Form\CompanyType;
use Vifeed\UserBundle\Form\ProfileType;
use JMS\DiExtraBundle\Annotation as DI;
use Vifeed\UserBundle\VifeedUserEvents;


/**
 * Class UserController
 *
 * @package Vifeed\UserBundle\Controller\Api
 */
class UserController extends FOSRestController
{

    /**
     * @DI\Inject("fos_user.user_manager")
     * @var UserManager
     */
    private $userManager;

    /**
     * @DI\Inject("event_dispatcher")
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * Информация о юзере
     *
     * @ApiDoc(
     *     section="User API",
     *     output={
     *          "class"="Vifeed\UserBundle\Entity\User",
     *          "groups"={"user"}
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         403="Returned when the user is not authorized to use this method"
     *     }
     * )
     *
     * @Rest\Get("users/current")
     *
     * @return Response
     */
    public function getUserAction()
    {
        $user = $this->getUser();

        $context = new SerializationContext();
        $context->setGroups(array('user'));

        $view = new View($user);

        $view->setSerializationContext($context);

        return $this->handleView($view);
    }

    /**
     * изменения профиля
     *
     * @ApiDoc(
     *     section="User API",
     *     input="Vifeed\UserBundle\Form\ProfileType",
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Returned when the something was wrong",
     *         403="Returned when the user is not authorized to use this method"
     *     }
     * )
     *
     * @Rest\Patch("users/current")
     *
     * @return Response
     */
    public function patchUserAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        $request = $this->container->get('request');

        if ($request->request->has('profile')) {
            $oldEmail = $user->getEmail();
            $form = $this->createForm(new ProfileType(), $user)
                         ->submit($request, false);
            if ($form->isValid()) {
                if ($user->getEmail() !== $oldEmail) {
                    $user->setEmailConfirmed(false);
                    $event = new FormEvent($form, $request);
                    $this->eventDispatcher->dispatch(VifeedUserEvents::CHANGE_EMAIL_SUCCESS, $event);
                }
            }

        } elseif ($request->request->has('change_password')) {
            $form = $this->createForm(new ChangePasswordFormType(), $user)
                         ->submit($request);

        } else {
            throw new BadRequestHttpException;
        }

        if ($form->isValid()) {
            $this->userManager->updateUser($user);

            $view = new View('');

        } else {
            $view = new View($form, 400);
        }

        return $this->handleView($view);
    }

    /**
     * Информация о юр.лице
     *
     * @ApiDoc(
     *     section="User API",
     *     output={
     *          "class"="Vifeed\UserBundle\Entity\Company"
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         403="Returned when the user is not authorized to use this method"
     *     }
     * )
     *
     * @Rest\Get("users/current/company")
     *
     * @return Response
     */
    public function getCompanyAction()
    {
        $user = $this->getUser();
        $company = $user->getCompany();
        if (!$company) {
            $company = '';
        }

        $view = new View($company, 200);

        return $this->handleView($view);
    }

    /**
     * Создать новую кампанию или изменить текущую
     *
     * @ApiDoc(
     *     section="User API",
     *     input="Vifeed\UserBundle\Entity\Company",
     *     output={
     *          "class"="Vifeed\UserBundle\Entity\Company"
     *     },
     *     statusCodes={
     *         200="Returned when changed successfully",
     *         201="Returned when created successfully",
     *         400="Returned when the something was wrong",
     *         403="Returned when the user is not authorized to use this method"
     *     }
     * )
     *
     * @Rest\Put("users/current/company")
     *
     * @return Response
     */
    public function putCompanyAction()
    {
        $existentCompany = $this->getUser()->getCompany();

        $form = $this->createForm(new CompanyType(), $existentCompany)
              ->submit($this->get('request'), $existentCompany ? false : true);

        if ($form->isValid()) {
            /** @var Company $company */
            $company = $form->getData();
            $company->setUser($this->getUser());

            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($company);
            $em->flush();

            $view = new View($company, $existentCompany ? 200 : 201);

        } else {
            $view = new View($form, 400);
        }

        return $this->handleView($view);
    }


}
 