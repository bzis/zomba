<?php

namespace Vifeed\SystemBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Vifeed\SystemBundle\Entity\AgeRange;

/**
 * Class AgeRangeController
 *
 * @package Vifeed\SystemBundle\Controller
 */
class AgeRangeController extends FOSRestController
{

    /**
     * @ApiDoc(
     *     section="Frontend API",
     *     resource=true
     * )
     *
     * @return Response
     */
    public function getAgerangesAction()
    {
        /** @var AgeRange[] $data */
        $data = $this->getDoctrine()->getRepository('VifeedSystemBundle:AgeRange')->findAll();

        $view = new View($data);

        return $this->handleView($view);
    }

}
