<?php

namespace Vifeed\UserBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;


/**
 * Class UserController
 *
 * @package Vifeed\UserBundle\Controller\Api
 */
class UserController extends FOSRestController
{
    /**
     * Информация о юзере по id
     *
     * @param int $id
     *
     * @ApiDoc(
     *     section="Frontend API"
     * )
     *
     * @return Response
     */
    public function getUserAction($id)
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->findUserBy(['id' => $id]);
        $view = new View(array(
                              'user' => $user,
                         ));

        return $this->handleView($view);
    }

}
 