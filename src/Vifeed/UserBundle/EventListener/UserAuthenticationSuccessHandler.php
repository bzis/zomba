<?php

namespace Vifeed\UserBundle\EventListener;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
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

    /**
     * @param WsseTokenManager $manager
     */
    public function __construct(WsseTokenManager $manager)
    {
        $this->wsseTokenManager = $manager;
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
        $wsseToken = $this->wsseTokenManager->createUserToken($token->getUser()->getId());

        $data = array(
            'success' => true,
            'token'   => $wsseToken
        );

        $response = new JsonResponse($data);
        $cookie = new Cookie('token', $wsseToken);
        $response->headers->setCookie($cookie);

        return $response;
    }
}
