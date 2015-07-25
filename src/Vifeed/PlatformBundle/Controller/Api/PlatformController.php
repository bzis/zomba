<?php

namespace Vifeed\PlatformBundle\Controller\Api;

use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\UnexpectedResultException;
use DoctrineExtensions\Taggable\TagManager;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\PlatformBundle\Entity\Platform;
use Vifeed\PlatformBundle\Entity\VkPlatform;
use Vifeed\PlatformBundle\Form\PlatformType;
use JMS\DiExtraBundle\Annotation as DI;
use Vifeed\PlatformBundle\Form\VkPlatformType;
use Vifeed\PlatformBundle\Manager\PlatformManager;
use Vifeed\SystemBundle\Helper\PaginationHelper;
use Vifeed\UserBundle\Entity\User;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Class PlatformController
 *
 * @package Vifeed\PlatformBundle\Controller
 *
 */
class PlatformController extends FOSRestController
{
    /**
     * @DI\Inject("doctrine.orm.entity_manager")
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @DI\Inject("vifeed.tag.manager")
     * @var TagManager
     */
    private $tagManager;

    /**
     * @DI\Inject("vifeed.platform.manager")
     * @var PlatformManager
     */
    private $platformManager;


    /**
     * Список площадок
     *
     * @ApiDoc(
     *     section="Platform API",
     *     resource=true,
     *     output={
     *          "class"="Vifeed\PlatformBundle\Entity\Platform",
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
    public function getPlatformsAction()
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->getType() != User::TYPE_PUBLISHER) {
            throw new AccessDeniedHttpException('Вы не можете просматривать площадки');
        }

        $platforms = $this->em->getRepository('VifeedPlatformBundle:Platform')->findByUser($user);
        foreach ($platforms as $platform) {
            $this->tagManager->loadTagging($platform);
        }

        $context = new SerializationContext();
        $context->setGroups(['own']);

        $view = new View($platforms);
        $view->setSerializationContext($context);

        return $this->handleView($view);
    }


    /**
     * Информация о площадке по id
     *
     * @param Platform $platform
     *
     * @Rest\Get("platforms/{id}", requirements={"id"="\d+"})
     * @ParamConverter("platform", class="VifeedPlatformBundle:Platform")
     * @ApiDoc(
     *     section="Platform API",
     *     requirements={
     *       {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="id площадки"}
     *     },
     *     output={
     *          "class"="Vifeed\PlatformBundle\Entity\Platform",
     *          "groups"={"own"}
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         403="Returned when the user is not authorized to use this method",
     *         404="Returned when platform not found"
     *     }
     * )
     *
     * @return Response
     */
    public function getPlatformAction(Platform $platform)
    {
        if ($this->getUser() !== $platform->getUser()) {
            throw new AccessDeniedHttpException('Вы не можете просматривать площадки');
        }

        $this->tagManager->loadTagging($platform);

        $context = new SerializationContext();
        $context->setGroups(['own']);

        $view = new View($platform);
        $view->setSerializationContext($context);

        return $this->handleView($view);
    }

    /**
     * Создать площадку
     *
     * @ApiDoc(
     *     section="Platform API",
     *     input="Vifeed\PlatformBundle\Form\PlatformType",
     *     output={
     *          "class"="Vifeed\PlatformBundle\Entity\Platform",
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
    public function putPlatformsAction()
    {
        if ($this->getUser()->getType() != User::TYPE_PUBLISHER) {
            throw new AccessDeniedHttpException('Вы не можете создавать площадки');
        }

        $form = $this->createPlatformForm();
        if ($form->isValid()) {
            /** @var Platform $platform */
            $platform = $form->getData();
            $platform->setUser($this->getUser());

            $this->platformManager->save($platform);

            $context = new SerializationContext();
            $context->setGroups(['own']);

            $view = new View($platform, 201);
            $view->setSerializationContext($context);

        } else {
            $view = new View($form, 400);
        }

        return $this->handleView($view);
    }

    /**
     * Редактирование площадки
     *
     * @param Platform $platform
     *
     * @Rest\Put("platforms/{id}", requirements={"id"="\d+"})
     * @ParamConverter("platform", class="VifeedPlatformBundle:Platform")
     * @ApiDoc(
     *     section="Platform API",
     *     requirements={
     *       {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="id площадки"}
     *     },
     *     input="Vifeed\PlatformBundle\Form\PlatformType",
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Returned when the something was wrong",
     *         403="Returned when the user is not authorized to use this method",
     *         404="Returned when platform not found"
     *     }
     * )
     *
     * @return Response
     */
    public function putPlatformAction(Platform $platform)
    {
        if ($platform->getUser() != $this->getUser()) {
            throw new AccessDeniedHttpException('Можно изменять только свои площадки');
        }

        $form = $this->createPlatformForm($platform);
        if ($form->isValid()) {
            $this->platformManager->save($platform);

            $view = new View('');

        } else {
            $view = new View($form, 400);
        }

        return $this->handleView($view);
    }

    /**
     * Удалить площадку
     *
     * @param Platform $platform
     *
     * @Rest\Delete("platforms/{id}", requirements={"id"="\d+"})
     * @ParamConverter("platform", class="VifeedPlatformBundle:Platform")
     * @ApiDoc(
     *     section="Platform API",
     *     requirements={
     *       {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="id площадки"}
     *     },
     *     statusCodes={
     *         204="Returned when successful",
     *         403="Returned when the user is not authorized to use this method",
     *         404="Returned when platform not found"
     *     }
     * )
     *
     * @return Response
     */
    public function deletePlatformAction(Platform $platform)
    {
        if ($platform->getUser() != $this->getUser()) {
            throw new AccessDeniedHttpException('Можно удалять только свои площадки');
        }

        $this->tagManager->deleteTagging($platform);
        $this->em->remove($platform);
        $this->em->flush();

        $view = new View('', 204);

        return $this->handleView($view);
    }

    /**
     * Постраничный список кампаний по площадке.
     * В сериализованные кампании также добавляется ключ banned = true|false
     *
     * @param Platform     $platform
     * @param ParamFetcher $paramFetcher
     *
     * @Rest\Get("platforms/{id}/campaigns", requirements={"id"="\d+"})
     * @ParamConverter("platform", class="VifeedPlatformBundle:Platform")
     * @ApiDoc(
     *     section="Platform API",
     *     requirements={
     *       {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="id площадки"}
     *     },
     *     output={
     *          "class"="Vifeed\CampaignBundle\Entity\Campaign",
     *          "groups"={"default"}
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         403="Returned when the user is not authorized to use this method",
     *         404="Returned when platform not found"
     *     }
     * )
     *
     * @Rest\QueryParam(name="page", requirements="\d+", default="1", description="page")
     * @Rest\QueryParam(name="per_page", requirements="\d+", default="10", description="campaigns per page")
     * @Rest\QueryParam(array=true, name="countries", requirements="\d+", description="country ids")
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     *
     * @return Response
     */
    public function getPlatformCampaignsAction(Platform $platform, ParamFetcher $paramFetcher)
    {
        if ($this->getUser()->getType() != User::TYPE_PUBLISHER) {
            throw new AccessDeniedHttpException();
        }

        if ($platform->getUser() != $this->getUser()) {
            throw new AccessDeniedHttpException();
        }

        $campaignRepo = $this->em->getRepository('VifeedCampaignBundle:Campaign');
        $banRepo = $this->em->getRepository('VifeedPlatformBundle:CampaignBan');

        $page = $paramFetcher->get('page');
        $perPage = $paramFetcher->get('per_page');
        $countries = $paramFetcher->get('countries');

        $params = [
              'status'    => Campaign::STATUS_ON,
              'countries' => $countries,
              'order' => [['bid', 'desc'], ['generalBudget', 'desc']]
        ];

        $campaignsPaginator = new PaginationHelper($campaignRepo->getCampaignsByParamsQuery($params), $perPage, $page);

        $campaigns = $campaignsPaginator->getItemsArray();
        $bans = $banRepo->findBannedCampaignsByPlatfrom($platform);

        $context = new SerializationContext();
        $context->setGroups(['default'])
                ->setAttribute('banned_campaigns', $bans);

        $view = new View($campaigns);
        $view->setSerializationContext($context);

        $response = $this->handleView($view);

        $routeParams = ['id' => $platform->getId(), 'per_page' => $perPage];
        $header = $campaignsPaginator->getLinkHeader($this->get('router'), 'api_get_platform_campaigns', $routeParams);
        $response->headers->add($header);

        return $response;
    }

    /**
     * Паблишер банит кампанию
     *
     * @param Platform $platform
     * @param Campaign $campaign
     *
     * @Rest\Put("platforms/{id}/ban/{campaign_id}", requirements={"id"="\d+", "campaign_id"="\d+"})
     * @ParamConverter("platform", class="VifeedPlatformBundle:Platform")
     * @ParamConverter("campaign", class="VifeedCampaignBundle:Campaign", options={"id" = "campaign_id"})
     * @ApiDoc(
     *     section="Platform API",
     *     requirements={
     *       {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="id площадки"},
     *       {"name"="campaign_id", "dataType"="integer", "requirement"="\d+", "description"="id кампании"}
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         403="Returned when the user is not authorized to use this method",
     *         404="Returned when platform or campaign not found"
     *     }
     * )
     *
     * @return Response
     */
    public function putCampaignBanAction(Platform $platform, Campaign $campaign)
    {
        if ($this->getUser()->getType() !== User::TYPE_PUBLISHER) {
            throw new AccessDeniedHttpException('Вы не можете блокировать кампании');
        }

        if ($platform->getUser() !== $this->getUser()) {
            throw new AccessDeniedHttpException('Можно работать только со своими площадками');
        }

        try {
            $this->platformManager->banCampaign($platform, $campaign);
        } catch (UnexpectedResultException $e) {
            throw new ConflictHttpException('Повторный бан невозможен');
        }

        $view = new View('', 201);

        return $this->handleView($view);
    }

    /**
     * Паблишер отменяет бан кампании
     *
     * @param Platform $platform id площадки
     * @param Campaign $campaign id кампании
     *
     * @Rest\Delete("platforms/{id}/ban/{campaign_id}", requirements={"id"="\d+", "campaign_id"="\d+"})
     * @ParamConverter("platform", class="VifeedPlatformBundle:Platform")
     * @ParamConverter("campaign", class="VifeedCampaignBundle:Campaign", options={"id" = "campaign_id"})
     * @ApiDoc(
     *     section="Platform API",
     *     requirements={
     *       {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="id площадки"},
     *       {"name"="campaign_id", "dataType"="integer", "requirement"="\d+", "description"="id кампании"}
     *     },
     *     statusCodes={
     *         204="Returned when successful",
     *         403="Returned when the user is not authorized to use this method",
     *         404="Returned when platform or campaign not found"
     *     }
     * )
     *
     *
     * @return Response
     */
    public function deleteCampaignBanAction(Platform $platform, Campaign $campaign)
    {
        if ($this->getUser()->getType() !== User::TYPE_PUBLISHER) {
            throw new AccessDeniedHttpException('Вы не можете блокировать кампании');
        }

        if ($platform->getUser() !== $this->getUser()) {
            throw new AccessDeniedHttpException('Можно работать только со своими площадками');
        }

        try {
            $this->platformManager->unbanCampaign($platform, $campaign);
        } catch (EntityNotFoundException $e) {
            throw new NotFoundHttpException();
        }
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
        if ($platform === null) {
            $platformType = $this->getRequest()->request->get('platform[type]', null, true);
            if ($platformType == 'vk') {
                $platformFormObject = new VkPlatformType($this->tagManager);
            } else {
                $platformFormObject = new PlatformType($this->tagManager);
            }
        } else {
            if ($platform instanceof VkPlatform) {
                $platformFormObject = new VkPlatformType($this->tagManager);
            } else {
                $platformFormObject = new PlatformType($this->tagManager);
            }
        }

        $form = $this->createForm($platformFormObject, $platform);
        $clearMissing = ($platform === null) ? true : false;
        $form->submit($this->getRequest(), $clearMissing);

        return $form;
    }

}
