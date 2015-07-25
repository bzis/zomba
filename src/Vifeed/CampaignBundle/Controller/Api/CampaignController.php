<?php

namespace Vifeed\CampaignBundle\Controller\Api;

use DoctrineExtensions\Taggable\TagManager;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\CampaignBundle\Exception\CampaignStatusException;
use Vifeed\CampaignBundle\Form\CampaignStatusType;
use Vifeed\CampaignBundle\Form\CampaignType;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Vifeed\CampaignBundle\Form\StartedCampaignType;
use Vifeed\CampaignBundle\Manager\CampaignManager;
use Vifeed\UserBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class CampaignController
 *
 * @package Vifeed\CampaignBundle\Controller
 */
class CampaignController extends FOSRestController
{

    /**
     * @DI\Inject("doctrine.orm.entity_manager")
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @DI\Inject("vifeed.campaign.manager")
     * @var CampaignManager
     */
    private $campaignManager;

    /**
     * @DI\Inject("vifeed.tag.manager")
     * @var TagManager
     */
    private $tagManager;


    /**
     * Список своих кампаний
     *
     * @ApiDoc(
     *     section="Campaign API",
     *     resource=true,
     *     output={
     *          "class"="Vifeed\CampaignBundle\Entity\Campaign",
     *          "groups"={"own"}
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         403="Returned when the user is not authorized to use this method"
     *     }
     * )
     *
     * @return Response
     */
    public function getCampaignsAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        $context = new SerializationContext();

        if ($user->getType() !== User::TYPE_ADVERTISER) {
            throw new AccessDeniedHttpException;
        }
        $campaignRepo = $this->em->getRepository('VifeedCampaignBundle:Campaign');

        /** @var Campaign[] $campaigns */
        $campaigns = $campaignRepo->findByUser($this->getUser());

        foreach ($campaigns as $campaign) {
            $this->tagManager->loadTagging($campaign);
        }

        $context->setGroups(['own']);

        $view = new View($campaigns);
        $view->setSerializationContext($context);

        return $this->handleView($view);
    }

    /**
     * Информация о кампании по id
     *
     * @param Campaign $campaign
     *
     * @Rest\Get("campaigns/{id}", requirements={"id"="\d+"})
     * @ParamConverter("campaign", class="VifeedCampaignBundle:Campaign")
     * @ApiDoc(
     *     section="Campaign API",
     *     requirements={
     *       {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="id кампании"}
     *     },
     *     output={
     *          "class"="Vifeed\CampaignBundle\Entity\Campaign",
     *          "groups"={"own", "default"}
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         403="Returned when the user is not authorized to use this method",
     *         404="Returned when campaign not found"
     *     }
     * )
     *
     * @return Response
     */
    public function getCampaignAction(Campaign $campaign)
    {
        $this->tagManager->loadTagging($campaign);

        $context = new SerializationContext();

        $userType = $this->getUser()->getType();

        if ($userType == User::TYPE_ADVERTISER) {
            if ($campaign->getUser() == $this->getUser()) {
                $context->setGroups(['own']);

            } else {
                throw new AccessDeniedHttpException();
            }

        } elseif ($userType == User::TYPE_PUBLISHER) {
            $context->setGroups(['default']);
        }

        $view = new View($campaign);
        $view->setSerializationContext($context);

        return $this->handleView($view);
    }

    /**
     * Создать новую кампанию
     *
     * @ApiDoc(
     *     section="Campaign API",
     *     input="Vifeed\CampaignBundle\Form\CampaignType",
     *     output={
     *          "class"="Vifeed\CampaignBundle\Entity\Campaign",
     *          "groups"={"own"}
     *     },
     *     statusCodes={
     *         201="Returned when successful",
     *         400="Returned when the something was wrong",
     *         403="Returned when the user is not authorized to use this method"
     *     }
     * )
     *
     * @return Response
     */
    public function putCampaignsAction()
    {
        if ($this->getUser()->getType() != User::TYPE_ADVERTISER) {
            throw new AccessDeniedHttpException('Вы не можете создавать кампании');
        }

        $form = $this->createCampaignForm();
        if ($form->isValid()) {
            /** @var Campaign $campaign */
            $campaign = $form->getData();
            $campaign->setUser($this->getUser());

            $this->campaignManager->save($campaign);

            $view = new View($campaign, 201);

            $context = new SerializationContext();
            $context->setGroups(['own']);

            $view->setSerializationContext($context);
        } else {
            $view = new View($form, 400);
        }

        return $this->handleView($view);
    }

    /**
     * Редактирование рекламной кампании
     * При удачном изменении данных возвращает пустой ответ и код 200
     *
     * @param Campaign $campaign
     *
     * @Rest\Put("campaigns/{id}", requirements={"id"="\d+"})
     * @ParamConverter("campaign", class="VifeedCampaignBundle:Campaign")
     * @ApiDoc(
     *     section="Campaign API",
     *     requirements={
     *       {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="id кампании"}
     *     },
     *     input="Vifeed\CampaignBundle\Form\CampaignType",
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Returned when the something was wrong",
     *         403="Returned when the user is not authorized to use this method",
     *         404="Returned when campaign not found"
     *     }
     * )
     *
     * @return Response
     */
    public function putCampaignAction(Campaign $campaign)
    {
        if ($campaign->getUser() != $this->getUser()) {
            throw new AccessDeniedHttpException('Можно изменять только свои кампании');
        }

        $form = $this->createCampaignForm($campaign);
        if ($form->isValid()) {
            $this->campaignManager->save($campaign);

            $view = new View('', 200);
        } else {
            $view = new View($form, 400);
        }

        return $this->handleView($view);
    }

    /**
     * Изменение статуса кампании
     *
     * @param Campaign $campaign
     *
     * @Rest\Put("campaigns/{id}/status", requirements={"id"="\d+"})
     * @ParamConverter("campaign", class="VifeedCampaignBundle:Campaign")
     * @ApiDoc(
     *     section="Campaign API",
     *     requirements={
     *       {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="id кампании"}
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Returned when the something was wrong",
     *         403="Returned when the user is not authorized to use this method",
     *         404="Returned when campaign not found"
     *     }
     * )
     *
     * @return Response
     */
    public function putCampaignStatusAction(Campaign $campaign)
    {
        if ($campaign->getUser() != $this->getUser()) {
            throw new AccessDeniedHttpException('Можно изменять только свои кампании');
        }

        $oldStatus = $campaign->getStatus();

        $form = $this->createForm(new CampaignStatusType(), $campaign);
        $form->submit($this->get('request'), false);

        if (!$form->isValid()) {
            return $this->handleView(new View($form, 400));
        }

        $newStatus = $campaign->getStatus();

        if ($newStatus == Campaign::STATUS_ON) { // включение
            try {
                $this->campaignManager->tryTurnStatusOn($campaign, $oldStatus);
            } catch (CampaignStatusException $e) {
                $form['status']->addError(new FormError($e->getMessage()));

                return $this->handleView(new View($form, 400));
            }
        } else { // пауза или архив
            if ($oldStatus == Campaign::STATUS_ON) {
                $canTurnOffByTime = $this->campaignManager->canTurnOffByTime($campaign);
                if ($canTurnOffByTime !== true) {
                    $msg = 'Невозможно остановить кампанию в течение двух часов после запуска. Вы сможете остановить кампанию через ' .
                          $canTurnOffByTime . ' мин.';
                    $form['status']->addError(new FormError($msg));

                    return $this->handleView(new View($form, 400));
                }
            }
            if ($newStatus == Campaign::STATUS_ARCHIVED) {
                $this->campaignManager->transferMoneyBackToUser($campaign);
            }
        }

        $this->campaignManager->save($campaign);

        $view = new View('', 200);

        return $this->handleView($view);
    }

    /**
     * Удалить кампанию
     *
     * @param Campaign $campaign
     *
     * @Rest\Delete("campaigns/{id}", requirements={"id"="\d+"})
     * @ParamConverter("campaign", class="VifeedCampaignBundle:Campaign")
     * @ApiDoc(
     *     section="Campaign API",
     *     requirements={
     *       {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="id кампании"}
     *     },
     *     statusCodes={
     *         204="Returned when successful",
     *         403="Returned when the user is not authorized to use this method",
     *         404="Returned when campaign not found"
     *     }
     * )
     *
     * @return Response
     */
    public function deleteCampaignAction(Campaign $campaign)
    {
        if ($campaign->getUser() != $this->getUser()) {
            throw new AccessDeniedHttpException('Можно удалять только свои кампании');
        }

        $this->tagManager->deleteTagging($campaign);
        $this->em->remove($campaign);
        $this->em->flush();

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
        if ($campaign && !in_array($campaign->getStatus(), [Campaign::STATUS_ARCHIVED, Campaign::STATUS_ENDED])) {
            $form = $this->createForm(new StartedCampaignType(), $campaign);
        } else {
            $form = $this->createForm(new CampaignType($this->getUser(), $this->tagManager), $campaign);
        }
        $clearMissing = ($campaign === null) ? true : false;
        $form->submit($this->get('request'), $clearMissing);

        return $form;
    }

}
