<?php

namespace Vifeed\UserBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints\Email;
use Vifeed\UserBundle\Entity\User;
use Vifeed\UserBundle\Form\ResettingFormType;

/**
 * Class ResettingController
 *
 * @package Vifeed\UserBundle\Controller\Api
 */
class ResettingController extends FOSRestController
{
    /**
     * сброс пароля
     */
    public function resetAction()
    {
        $data = '';
        $code = 200;

        if ($email = $this->getRequest()->request->get('email')) {
            $this->sendEmail($email);
        } elseif ($token = $this->getRequest()->request->get('token')) {
            $data = $this->reset($token);
            if ($data instanceof Form) {
                $code = 400;
            }
//            $this->forward('FOSUserBundle:Resetting:reset', ['request' => $this->getRequest(), 'token' => $token]);
        } else {
            throw new BadRequestHttpException();
        }
        $view = new View($data, $code);

        return $this->handleView($view);
    }

    /**
     * отправка письма с токеном
     */
    protected function sendEmail($email)
    {
        $emailConstraint = new Email(['message' => 'Неверный email']);
        $errors = $this->get('validator')->validateValue($email, $emailConstraint);

        if (count($errors) != 0) {
            throw new BadRequestHttpException($errors[0]->getMessage());
        }

        /** @var $user User */
        $user = $this->container->get('fos_user.user_manager')->findUserByEmail($email);

        if (null === $user) {
            throw new BadRequestHttpException('У нас нет пользователя с таким адресом');
        }

        if (!$user->isEnabled()) {
            throw new BadRequestHttpException('Пользователь заблокирован');
        }

        if ($user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
            throw new BadRequestHttpException('Срок сброса пароля истёк');
        }

        if (null === $user->getConfirmationToken()) {
            /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
            $tokenGenerator = $this->container->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
        }

        $this->container->get('fos_user.mailer')->sendResettingEmailMessage($user);
        $user->setPasswordRequestedAt(new \DateTime());
        $this->container->get('fos_user.user_manager')->updateUser($user);
    }

    /**
     * Reset user password
     */
    public function reset($token)
    {
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->container->get('fos_user.user_manager');

        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new BadRequestHttpException('Пользователь не найден');
        }

        $form = $this->createForm(new ResettingFormType());
        $form->setData($user);
        $form->submit($this->getRequest());

        if ($form->isValid()) {
            $user->setConfirmationToken(null);
            $userManager->updateUser($user);

            return '';
        }

        return $form;
    }

}