<?php

namespace Vifeed\UserBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Vifeed\UserBundle\Entity\User;
use Vifeed\UserBundle\Entity\UserIpLog;
use Vifeed\UserBundle\Manager\WsseTokenManager;

/**
 * Class UserAuthenticationSuccessHandler
 *
 * @package Vifeed\UserBundle\EventListener
 */
class UserAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    /** @var WsseTokenManager */
    private $wsseTokenManager;
    private $entityManager;

    /**
     * @param WsseTokenManager $manager
     */
    public function __construct(WsseTokenManager $manager, EntityManager $entityManager)
    {
        $this->wsseTokenManager = $manager;
        $this->entityManager = $entityManager;
    }

    /**
     * This is called when an interactive authentication attempt succeeds. This
     * is called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @param Request        $request
     * @param TokenInterface $token
     *
     * @return Response never null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $wsseToken = $this
              ->wsseTokenManager
              ->saveForALongTime((boolean) $request->request->get('_remember_me'))
              ->createUserToken($token->getUser()->getId());

        /** @var User $user */
        $user = $token->getUser();

        $ips = $request->headers->get('x-forwarded-for');
        $ips = explode(', ', $ips);
        $ip = $ips[0];
        if (!$ip) {
            $ip = $request->getClientIp();
        }

        $log = new UserIpLog($user, $ip);
        $this->entityManager->persist($log);
        $this->entityManager->flush();

        $data = [
              'token'      => $wsseToken,
              'type'       => $user->getType(),
              'first_name' => $user->getFirstName(),
              'surname'    => $user->getSurname()
        ];

        return new JsonResponse($data);
    }
}
