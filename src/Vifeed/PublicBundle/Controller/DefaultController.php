<?php

namespace Vifeed\PublicBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('VifeedPublicBundle:Default:homepage.html.twig', []);
    }
}
