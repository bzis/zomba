<?php

namespace Vifeed\PlatformBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Vifeed\PlatformBundle\Entity\Platform;
use Vifeed\PlatformBundle\Form\PlatformType;

/**
 * Class PlatformController
 *
 * @package Vifeed\PlatformBundle\Controller
 *
 */
class PlatformController extends FOSRestController
{
    /**
     * Информация о площадке по id
     *
     * @param int $id
     *
     * @ApiDoc(
     *     section="Frontend API"
     * )
     *
     * @return Response
     */
    public function getPlatformAction($id)
    {
        $platform = $this->getEntity($id);
        $view = new View(array(
                              'platform' => $platform,
                         ));

        return $this->handleView($view);
    }

    /**
     * Создать площадку
     *
     * @ApiDoc(
     *     section="Frontend API",
     *     resource=true,
     *     input="Vifeed\CampaignBundle\Entity\Platform"
     * )
     *
     * @return Response
     */
    public function putPlatformsAction()
    {
        $form = $this->createPlatformForm();
        if ($form->isValid()) {
            $platform = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($platform);
            $em->flush();

            $view = new View($platform, 201);
        } else {
            $view = new View($form, 400);
        }

        return $this->handleView($view);
    }

    /**
     * Редактирование площадки
     *
     * @param int $id
     *
     * @ApiDoc(
     *     section="Frontend API",
     *     input="Vifeed\CampaignBundle\Entity\Platform"
     *     input="Vifeed\PlatformBundle\Entity\Platform"
     * )
     *
     * @return Response
     */
    public function putPlatformAction($id)
    {
        $platform = $this->getEntity($id);
        $form = $this->createPlatformForm($platform);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $view = new View($platform, 200);
        } else {
            $view = new View($form, 400);
        }

        return $this->handleView($view);
    }

    /**
     * Удалить площадку
     *
     * @param int $id
     *
     * @ApiDoc(
     *     section="Frontend API"
     * )
     *
     * @return Response
     */
    public function deletePlatformAction($id)
    {
        $platform = $this->getEntity($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($platform);
        $em->flush();

        $view = new View('', 204);

        return $this->handleView($view);
    }

    /**
     * createPlatformForm
     *
     * @param null|Platform $platform
     *
     * @return \Symfony\Component\Form\Form
     */
    private function createPlatformForm($platform = null)
    {
        $form = $this->createForm(new PlatformType(), $platform);
        $clearMissing = ($platform === null) ? true : false;
        $form->submit($this->getRequest(), $clearMissing); // todo: clearMissing - посмотреть как себя ведёт

        return $form;
    }

    /**
     * @param int $id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return \Vifeed\PlatformBundle\Entity\Platform
     */
    private function getEntity($id)
    {
        $id += 0;
        if ($id == 0) {
            throw new BadRequestHttpException('Incorrect id');
        }
        $data = $this->getDoctrine()->getRepository('VifeedPlatformBundle:Platform')->find($id);
        if (!$data instanceof Platform) {
            throw new NotFoundHttpException('Platform not found');
        }

        return $data;
    }
}
