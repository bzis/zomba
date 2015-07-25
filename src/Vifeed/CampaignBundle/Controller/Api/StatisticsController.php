<?php

namespace Vifeed\CampaignBundle\Controller\Api;

use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\GeoBundle\Entity\Country;
use Vifeed\SystemBundle\Controller\RestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Vifeed\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Class GeoStatsController
 * @package Vifeed\CampaignBundle\Controller\Api
 */
class StatisticsController extends RestController
{

    /**
     * Количество просмотров по городам
     *
     * @param Campaign $campaign
     *
     * @Rest\Get("campaigns/{id}/statistics/geo", requirements={"id"="\d+"})
     * @ParamConverter("campaign", class="VifeedCampaignBundle:Campaign")
     * @ApiDoc(
     *     section="Campaign statistics API",
     *     requirements={
     *       {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="id кампании"}
     *     },
     *     parameters={
     *       {"name"="date_from", "dataType"="date", "format"="YYYY-MM-DD", "required"=true},
     *       {"name"="date_to", "dataType"="date", "format"="YYYY-MM-DD", "required"=true}
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Returned when date_from or date_to is not correct",
     *         403="Returned when the user is not authorized to use this method",
     *         404="Returned when campaign not found"
     *     }
     * )
     */
    public function getCitiesStatsAction(Campaign $campaign)
    {
        $this->checkPermissions($campaign);
        $dates = $this->getRequestedDates();

        $repo = $this->getDoctrine()->getRepository('VifeedVideoViewBundle:VideoView');
        $stats = $repo->getGeoViewsByCity($campaign, $dates['date_from'], $dates['date_to']);

        $view = new View($stats);

        return $this->handleView($view);
    }

    /**
     * Количество просмотров по странам
     *
     * @param Campaign $campaign
     *
     * @Rest\Get("campaigns/{id}/statistics/geo/countries", requirements={"id"="\d+"})
     * @ParamConverter("campaign", class="VifeedCampaignBundle:Campaign")
     * @ApiDoc(
     *     section="Campaign statistics API",
     *     requirements={
     *       {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="id кампании"}
     *     },
     *     parameters={
     *       {"name"="date_from", "dataType"="date", "format"="YYYY-MM-DD", "required"=true},
     *       {"name"="date_to", "dataType"="date", "format"="YYYY-MM-DD", "required"=true}
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Returned when date_from or date_to is not correct",
     *         403="Returned when the user is not authorized to use this method",
     *         404="Returned when campaign not found"
     *     }
     * )
     */
    public function getCountriesStatsAction(Campaign $campaign)
    {
        $this->checkPermissions($campaign);
        $dates = $this->getRequestedDates();

        $repo = $this->getDoctrine()->getRepository('VifeedVideoViewBundle:VideoView');
        $stats = $repo->getGeoViewsByCountry($campaign, $dates['date_from'], $dates['date_to']);

        $totalViews = 0;
        foreach ($stats as $country) {
            $totalViews += $country['views'];
        }
        foreach ($stats as &$country) {
            $country['percentage'] = round($country['views'] / $totalViews * 100);
        }

        $view = new View($stats);

        return $this->handleView($view);
    }

    /**
     * Количество просмотров по стране
     *
     * @param Campaign $campaign
     * @param Country  $country
     *
     * @Rest\Get("campaigns/{id}/statistics/geo/countries/{country_id}", requirements={"id"="\d+", "country_id"="\d+"})
     * @ParamConverter("campaign", class="VifeedCampaignBundle:Campaign")
     * @ParamConverter("country", class="VifeedGeoBundle:Country", options={"id" = "country_id"})
     * @ApiDoc(
     *     section="Campaign statistics API",
     *     requirements={
     *       {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="id кампании"},
     *       {"name"="country_id", "dataType"="integer", "requirement"="\d+", "description"="id страны"}
     *     },
     *     parameters={
     *       {"name"="date_from", "dataType"="date", "format"="YYYY-MM-DD", "required"=true},
     *       {"name"="date_to", "dataType"="date", "format"="YYYY-MM-DD", "required"=true}
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Returned when date_from or date_to is not correct",
     *         403="Returned when the user is not authorized to use this method",
     *         404="Returned when campaign or country not found"
     *     }
     * )
     */
    public function getCitiesStatsByCountryAction(Campaign $campaign, Country $country)
    {
        $this->checkPermissions($campaign);
        $dates = $this->getRequestedDates();

        $repo = $this->getDoctrine()->getRepository('VifeedVideoViewBundle:VideoView');

        $stats = $repo->getGeoViewsByCity($campaign, $dates['date_from'], $dates['date_to'], $country);

        $totalViews = 0;
        foreach ($stats as $city) {
            $totalViews += $city['views'];
        }
        foreach ($stats as &$city) {
            $city['percentage'] = round($city['views'] / $totalViews * 100);
        }

        $view = new View($stats);

        return $this->handleView($view);
    }

    /**
     * Количество просмотров и показов по дням
     *
     * @param Campaign $campaign
     *
     * @Rest\Get("campaigns/{id}/statistics/daily", requirements={"id"="\d+"})
     * @ParamConverter("campaign", class="VifeedCampaignBundle:Campaign")
     * @ApiDoc(
     *     section="Campaign statistics API",
     *     requirements={
     *       {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="id кампании"}
     *     },
     *     parameters={
     *       {"name"="date_from", "dataType"="date", "format"="YYYY-MM-DD", "required"=true},
     *       {"name"="date_to", "dataType"="date", "format"="YYYY-MM-DD", "required"=true}
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Returned when date_from or date_to is not correct",
     *         403="Returned when the user is not authorized to use this method",
     *         404="Returned when campaign not found"
     *     }
     * )
     *
     */
    public function getDailyStatsAction(Campaign $campaign)
    {
        $this->checkPermissions($campaign);
        $dates = $this->getRequestedDates();

        $statsManager = $this->container->get('vifeed.videoview.stats_manager');
        $stats = $statsManager->getDailyStatsByCampaigns($campaign, $dates['date_from'], $dates['date_to']);

        $view = new View($stats);

        return $this->handleView($view);
    }

    /**
     * Количество показов и просмотров по часам за вчера или сегодня
     *
     * @param Campaign $campaign
     * @param string   $day
     *
     * @Rest\Get("campaigns/{id}/statistics/hourly/{day}", requirements={"id"="\d+", "day"="today|yesterday"})
     * @ParamConverter("campaign", class="VifeedCampaignBundle:Campaign")
     * @ApiDoc(
     *     section="Campaign statistics API",
     *     requirements={
     *       {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="id кампании"}
     *     },
     *     parameters={
     *       {"name"="date_from", "dataType"="date", "format"="YYYY-MM-DD", "required"=true},
     *       {"name"="date_to", "dataType"="date", "format"="YYYY-MM-DD", "required"=true}
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Returned when date_from or date_to is not correct",
     *         403="Returned when the user is not authorized to use this method",
     *         404="Returned when campaign not found"
     *     }
     * )
     *
     */
    public function getHourlyStatsAction(Campaign $campaign, $day)
    {
        $this->checkPermissions($campaign);

        $dateFrom = new \DateTime($day);
        $dateTo = clone $dateFrom;
        $dateTo->setTime(23, 59, 59);

        $repo = $this->getDoctrine()->getRepository('VifeedVideoViewBundle:VideoView');
        $stats = $repo->getHourlyViewsAndShows($campaign, $dateFrom, $dateTo);

        $view = new View($stats);

        return $this->handleView($view);
    }

    /**
     * @param Campaign $campaign
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    private function checkPermissions(Campaign $campaign)
    {
        if ($this->getUser()->getType() !== User::TYPE_ADVERTISER) {
            throw new AccessDeniedHttpException;
        }

        if ($this->getUser() !== $campaign->getUser()) {
            throw new AccessDeniedHttpException;
        }
    }
}