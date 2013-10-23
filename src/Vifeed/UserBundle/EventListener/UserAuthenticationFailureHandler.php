<?php

namespace Vifeed\UserBundle\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

/**
 * Class UserAuthenticationFailureHandler
 *
 * @package Vifeed\UserBundle\EventListener
 */
class UserAuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{

    /**
     * This is called when an interactive authentication attempt fails. This is
     * called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @return Response The response to return, never null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        //todo: оно надо?
        $request->getSession()->set(SecurityContextInterface::AUTHENTICATION_ERROR, $exception);

        $data = array(
            'success' => false,
            'message' => $exception->getMessage() //todo: перевести message
        );

        return new JsonResponse($data, 401);
    }
}
