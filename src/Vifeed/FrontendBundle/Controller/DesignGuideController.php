<?php

namespace Vifeed\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DesignGuideController extends Controller
{
    public function indexAction()
    {
        return $this->render('VifeedFrontendBundle:DesignGuide:design_guide.html.twig');
    }
}
