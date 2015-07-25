<?php
namespace Vifeed\UserBundle\EventListener;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Vifeed\UserBundle\Entity\User;
use Vifeed\UserBundle\Manager\UserNotificationManager;
use Vifeed\UserBundle\NotificationEvent\EmailConfirmationEvent;
use Vifeed\UserBundle\VifeedUserEvents;

/**
 * Class EmailConfirmationListener
 *
 * переопределии потому что стандартый лисенер всегда ставит $user->setEnabled(false),
 * а нам это не подходит, потому что рекламодатель сразу enabled = true
 *
 * @package Vifeed\UserBundle\EventListener
 */
class EmailConfirmationListener implements EventSubscriberInterface
{
    private $notificationManager;
    private $tokenGenerator;
    private $router;
    private $session;

    /**
     * @param UserNotificationManager $notificationManager
     * @param TokenGeneratorInterface $tokenGenerator
     * @param UrlGeneratorInterface   $router
     * @param SessionInterface        $session
     */
    public function __construct(
          UserNotificationManager $notificationManager,
          TokenGeneratorInterface $tokenGenerator,
          UrlGeneratorInterface $router,
          SessionInterface $session
    ) {
        $this->notificationManager = $notificationManager;
        $this->tokenGenerator = $tokenGenerator;
        $this->router = $router;
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
              FOSUserEvents::REGISTRATION_SUCCESS    => 'onRegistrationSuccess',
              VifeedUserEvents::CHANGE_EMAIL_SUCCESS => 'onChangeEmailSuccess'
        );
    }

    /**
     * @param FormEvent $event
     */
    public function onRegistrationSuccess(FormEvent $event)
    {
        /** @var $user \FOS\UserBundle\Model\UserInterface */
        $user = $event->getForm()->getData();

        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
        }

        $this->sendEmail($user);

        $this->session->set('fos_user_send_confirmation_email/email', $user->getEmail());

        $url = $this->router->generate('fos_user_registration_check_email');
        $event->setResponse(new RedirectResponse($url));
    }

    /**
     * @param FormEvent $event
     */
    public function onChangeEmailSuccess(FormEvent $event)
    {
        /** @var $user \FOS\UserBundle\Model\UserInterface */
        $user = $event->getForm()->getData();

        $user->setConfirmationToken($this->tokenGenerator->generateToken());

        $this->sendEmail($user);

        $this->session->set('fos_user_send_confirmation_email/email', $user->getEmail());
    }

    /**
     *
     * @param \Vifeed\UserBundle\Entity\User $user
     */
    private function sendEmail(User $user)
    {
        $url = $this->router->generate('fos_user_registration_confirm', array('token' => $user->getConfirmationToken()), true);

        $parameters = [
              'user'            => $user,
              'confirmationUrl' => $url
        ];
        $this->notificationManager->notify($user, new EmailConfirmationEvent(), $parameters);
    }
}
