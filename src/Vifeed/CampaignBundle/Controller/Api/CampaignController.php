<?php

namespace Vifeed\CampaignBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\CampaignBundle\Form\CampaignType;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class CampaignController
 *
 * @package Vifeed\CampaignBundle\Controller
 */
class CampaignController extends FOSRestController
{

    /**
     * Список кампаний
     *
     * @ApiDoc()
     *
     * @return Response
     */
    public function getCampaignsAction()
    {
        $data = array();
        /** @var Campaign[] $data */
        $campaigns = $this->getDoctrine()->getRepository('VifeedCampaignBundle:Campaign')->findAll();
        foreach ($campaigns as $campaign) {
            $data[] = array(
                'campaign' => $campaign,
                'actions'  => $this->getActions($campaign)
            );
        }

        $view = new View($data);

        return $this->handleView($view);
    }

    /**
     * Информация о кампании по id
     *
     * @param int $id
     *
     * @ApiDoc()
     *
     * @//QueryParam(name="id", requirements="\d+", strict=true, description="blabal", nullable=false)
     *
     * @return Response
     */
    public function getCampaignAction($id)
    {
        $campaign = $this->getEntity($id);
        $view = new View(array(
                              'campaign' => $campaign,
                              'actions'  => $this->getActions($campaign)
                         ));

        return $this->handleView($view);
    }

    /**
     * Создать новую кампанию
     *
     * @ApiDoc(
     *     input="Vifeed\CampaignBundle\Entity\Campaign"
     * )
     *
     * @return Response
     */
    public function putCampaignsAction()
    {
        $form = $this->createCampaignForm();
        if ($form->isValid()) {
            $campaign = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($campaign);
            $em->flush();

            $view = new View($campaign, 201);
        } else {
            $view = new View($form, 400);
        }

        return $this->handleView($view);
    }

    /**
     * Редактирование рекламной кампании
     *
     * @param int $id
     *
     * @ApiDoc(
     *     input="Vifeed\CampaignBundle\Entity\Campaign"
     * )
     *
     * @return Response
     */
    public function putCampaignAction($id)
    {
        $campaign = $this->getEntity($id);
        $form = $this->createCampaignForm($campaign);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $view = new View($campaign, 200);
        } else {
            $view = new View($form, 400);
        }

        return $this->handleView($view);
    }

    /**
     * Удалить кампанию
     *
     * @param int $id
     *
     * @ApiDoc()
     *
     * @return Response
     */
    public function deleteCampaignAction($id)
    {
        $campaign = $this->getEntity($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($campaign);
        $em->flush();

        $view = new View('', 204);

        return $this->handleView($view);
    }

    /**
     * createCampaignForm
     *
     * @param null|Campaign $campaign
     *
     * @return \Symfony\Component\Form\Form
     */
    private function createCampaignForm($campaign = null)
    {
        $form = $this->createForm(new CampaignType(), $campaign);
        $clearMissing = ($campaign === null) ? true : false;
        $form->submit($this->getRequest(), $clearMissing); // todo: clearMissing - посмотреть как себя ведёт

        return $form;
    }

    /**
     * @param int $id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return \Vifeed\CampaignBundle\Entity\Campaign
     */
    private function getEntity($id)
    {
        $id += 0;
        if ($id == 0) {
            throw new BadRequestHttpException('Incorrect id');
        }
        $data = $this->getDoctrine()->getRepository('VifeedCampaignBundle:Campaign')->find($id);
        if (!$data instanceof Campaign) {
            throw new NotFoundHttpException('Campaign not found');
        }

        return $data;
    }

    /**
     * @param \Vifeed\CampaignBundle\Entity\Campaign $campaign
     *
     * todo: можно возвращать только один урл, потому что они все одинаковые
     *
     * @return array
     */
    private function getActions(Campaign $campaign)
    {
        return array(
            'get'    => $this->get('router')->generate('api_get_campaign', array('id' => $campaign->getId())),
            'edit'   => $this->get('router')->generate('api_put_campaign', array('id' => $campaign->getId())),
            'delete' => $this->get('router')->generate('api_delete_campaign', array('id' => $campaign->getId())),
        );
    }

}
