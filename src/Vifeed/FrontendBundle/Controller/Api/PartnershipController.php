<?php

namespace Vifeed\FrontendBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Vifeed\FrontendBundle\Form\PartnershipType;

/**
 * Class PartnershipController
 * @package Vifeed\FrontendBundle\Api
 */
class PartnershipController extends FOSRestController
{

    /**
     * заявка на партнёрство
     *
     * @Rest\Put("partnership")
     * @ApiDoc(
     *     section="Frontend API",
     *     input="Vifeed\FrontendBundle\Form\PartnershipType",
     *     statusCodes={
     *         201="Returned when successful",
     *         400="Returned when the something was wrong"
     *     }
     * )
     *
     * @return Response
     */
    public function putPartnershipAction()
    {
        $form = $this->createForm(new PartnershipType());
        $form->submit($this->get('request'));
        if ($form->isValid()) {
            $data = $form->getData();
            $mailer = $this->get('vifeed.mailer');
            $message = $mailer->renderMessage('VifeedFrontendBundle:Email:partnership.html.twig', $data);
            $emailTo = $this->container->getParameter('partnership.notification.email');
            $mailer->sendMessage('Предложение партнёрства', $message, $emailTo, $data['email']);

            $view = new View('', 201);
        } else {
            $view = new View($form, 400);
        }

        return $this->handleView($view);
    }
} 