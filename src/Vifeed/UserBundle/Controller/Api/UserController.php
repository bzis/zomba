<?php

namespace Vifeed\UserBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Vifeed\UserBundle\Entity\User;


/**
 * Class UserController
 *
 * @package Vifeed\UserBundle\Controller\Api
 */
class UserController extends FOSRestController
{
    /**
     * Информация о юзере
     *
     * @ApiDoc(
     *     section="Frontend API"
     * )
     *
     * @return Response
     */
    public function getUserAction()
    {
        $user = $this->getUser();

        $context = new SerializationContext();
        $context->setGroups(array('user'));

        $view = new View(array(
                              'user' => $user,
                         ));

        $view->setSerializationContext($context);

        return $this->handleView($view);
    }

}
 