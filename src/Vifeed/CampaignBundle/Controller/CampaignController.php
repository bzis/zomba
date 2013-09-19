<?php

namespace Vifeed\CampaignBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\RestBundle\View\View;

class CampaignController extends FOSRestController
{
    /**
     * @Route("/campaigns")
     */
    public function allAction()
    {
        $data = $this->getDoctrine()->getRepository('CampaignBundle:Campaign')->findAll();
        $view = View::create($data)
                ->setFormat('json');
        return $this->handleView($view);;
    }

    /**
     * @Route("/campaigns/{id}")
     */
    public function getAction($id)
    {
    }

}
