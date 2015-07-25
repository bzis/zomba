<?php

namespace Vifeed\UserBundle\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class PasswordController
 *
 * @package Vifeed\UserBundle\Controller|Frontend
 */
class PasswordController extends Controller
{
    /**
     * A password generation template
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('VifeedUserBundle:Frontend:layout.html.twig', []);
    }
}
