<?php

namespace Vifeed\UserBundle\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class UserController
 *
 * @package Vifeed\UserBundle\Controller|Frontend
 */
class UserController extends Controller
{
    /**
     * Регистрация нового юзера
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('VifeedUserBundle:Frontend:layout.html.twig', []);
    }
}
