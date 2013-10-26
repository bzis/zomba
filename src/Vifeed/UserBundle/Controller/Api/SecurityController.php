<?php

namespace Vifeed\UserBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class SecurityController
 *
 * @package Vifeed\UserBundle\Controller
 */
class SecurityController extends FOSRestController
{

    /**
     * Удалить токен и разлогиниться
     *
     * @ApiDoc(
     *     section="Frontend API"
     * )
     *
     * @return Response
     */
    public function deleteUsersTokenAction()
    {
        $tokenManager = $this->container->get('vifeed.user.wsse_token_manager');
        $tokenManager->deleteUserToken($this->getUser()->getId());
        $this->get('security.context')->setToken(null);
        $this->get('request')->getSession()->invalidate();
        $view = new View('', 204);

        return $this->handleView($view);
    }
}
