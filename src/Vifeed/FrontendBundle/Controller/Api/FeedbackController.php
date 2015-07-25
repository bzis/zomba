<?php

namespace Vifeed\FrontendBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Vifeed\FrontendBundle\Form\FeedbackType;

/**
 * Class FeedbackController
 * @package Vifeed\FrontendBundle\Api
 */
class FeedbackController extends FOSRestController
{

    /**
     * заявка на партнёрство
     *
     * @Rest\Put("feedback")
     * @ApiDoc(
     *     section="Frontend API",
     *     input="Vifeed\FrontendBundle\Form\FeedbackType",
     *     statusCodes={
     *         201="Returned when successful",
     *         400="Returned when the something was wrong"
     *     }
     * )
     *
     * @return Response
     */
    public function putFeedbackAction()
    {
        $form = $this->createForm(new FeedbackType());
        $form->submit($this->get('request'));
        if ($form->isValid()) {
            $data = $form->getData();
            $mailer = $this->get('vifeed.mailer');
            $message = $mailer->renderMessage('VifeedFrontendBundle:Email:feedback.html.twig', $data);
            $emailTo = $this->container->getParameter('feedback.notification.email');
            $mailer->sendMessage('Обратная связь', $message, $emailTo, $data['email']);

            $view = new View('', 201);
        } else {
            $view = new View($form, 400);
        }

        return $this->handleView($view);
    }
} 