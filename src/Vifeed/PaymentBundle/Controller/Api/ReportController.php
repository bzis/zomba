<?php


namespace Vifeed\PaymentBundle\Controller\Api;

use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\PaymentBundle\Entity\Order;
use Vifeed\PaymentBundle\Entity\Withdrawal;
use Vifeed\PlatformBundle\Entity\Platform;
use Vifeed\SystemBundle\Controller\RestController;
use Vifeed\UserBundle\Entity\User;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Class ReportController
 *
 * @package Vifeed\PaymentBundle\Controller\Api
 */
class ReportController extends RestController
{

    /**
     * статистика по всем кампаниям
     *
     * @ApiDoc(
     *     section="Billing statistics API",
     *     resource=true,
     *     parameters={
     *       {"name"="date_from", "dataType"="date", "format"="YYYY-MM-DD", "required"=true},
     *       {"name"="date_to", "dataType"="date", "format"="YYYY-MM-DD", "required"=true}
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Returned when date_from or date_to is not correct",
     *         403="Returned when the user is not authorized to use this method"
     *     }
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getBillingSpendingsAction()
    {
        if ($this->getUser()->getType() !== User::TYPE_ADVERTISER) {
            throw new AccessDeniedHttpException;
        }

        $dates = $this->getRequestedDates();

        $campaignRepo = $this->getDoctrine()->getRepository('VifeedCampaignBundle:Campaign');

        $campaigns = $campaignRepo->findByUserIndexed($this->getUser(), true);

        $data = ['campaigns' => [], 'total_charged' => 0, 'total_views' => 0, 'total_paid_views' => 0, 'total_kpi' => 0];

        if ($campaigns) {
            $statsManager = $this->container->get('vifeed.videoview.stats_manager');
            $stats = $statsManager->getOverallStatsByCampaigns($campaigns, $dates['date_from'], $dates['date_to']);

            foreach ($stats as $row) {
                $data['campaigns'][] = [
                      'id'         => $row['campaign_id'],
                      'hash_id'    => $campaigns[$row['campaign_id']]->getHashId(),
                      'name'       => $campaigns[$row['campaign_id']]->getName(),
                      'views'      => $row['views'],
                      'paid_views' => $row['paid_views'],
                      'charged'    => $row['charged'],
                      'kpi'        => $row['paid_views'] > 0 ? round($row['views'] / $row['paid_views'], 2) : 0
                ];
                $data['total_charged'] += $row['charged'];
                $data['total_views'] += $row['views'];
                $data['total_paid_views'] += $row['paid_views'];
            }
            if ($data['total_paid_views'] > 0) {
                $data['total_kpi'] = round($data['total_views'] / $data['total_paid_views'], 2);
            }
        }

        $view = new View($data);

        return $this->handleView($view);
    }

    /**
     * статистика по дням по кампании
     *
     * @param Campaign $campaign id кампании
     *
     * @Rest\Get("billing/spendings/{campaign_id}", requirements={"campaign_id"="\d+"})
     * @ParamConverter("campaign", class="VifeedCampaignBundle:Campaign",
     *                  options={"id" = "campaign_id", "repository_method" = "findWithoutFilter"})
     * @ApiDoc(
     *     section="Billing statistics API",
     *     requirements={
     *       {"name"="campaign_id", "dataType"="integer", "requirement"="\d+", "description"="id кампании"}
     *     },
     *     parameters={
     *       {"name"="date_from", "dataType"="date", "format"="YYYY-MM-DD", "required"=true},
     *       {"name"="date_to", "dataType"="date", "format"="YYYY-MM-DD", "required"=true}
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Returned when date_from or date_to is not correct",
     *         403="Returned when the user is not authorized to use this method",
     *         404="Returned when campaign is not found"
     *     }
     * )
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getBillingSpendingsByCampaignAction(Campaign $campaign)
    {
        if ($this->getUser()->getType() !== User::TYPE_ADVERTISER) {
            throw new AccessDeniedHttpException;
        }

        if ($this->getUser() !== $campaign->getUser()) {
            throw new AccessDeniedHttpException;
        }

        $dates = $this->getRequestedDates();

        $data = ['stats' => [], 'total_views' => 0, 'total_paid_views' => 0, 'total_charged' => 0, 'total_kpi' => 0];

        $statsManager = $this->container->get('vifeed.videoview.stats_manager');
        $stats = $statsManager->getDailyStatsByCampaigns($campaign, $dates['date_from'], $dates['date_to']);

        foreach ($stats as &$row) {
            unset($row['campaign_id']);
            $row['charged'] += 0; // если не было списаний, то приходит null
            $row['kpi'] = $row['paid_views'] > 0 ? round($row['views'] / $row['paid_views'], 2) : 0;

            $data['total_views'] += $row['views'];
            $data['total_paid_views'] += $row['paid_views'];
            $data['total_charged'] += $row['charged'];
        }

        $data['stats'] = $stats;
        $data['total_kpi'] = $data['total_paid_views'] > 0 ? round($data['total_views'] / $data['total_paid_views'], 2) : 0;
        $view = new View($data);

        return $this->handleView($view);
    }

    /**
     * платежи рекламодателя
     *
     * @ApiDoc(
     *     section="Billing statistics API",
     *     resource=true,
     *     parameters={
     *       {"name"="date_from", "dataType"="date", "format"="YYYY-MM-DD", "required"=true},
     *       {"name"="date_to", "dataType"="date", "format"="YYYY-MM-DD", "required"=true}
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Returned when date_from or date_to is not correct",
     *         403="Returned when the user is not authorized to use this method"
     *     }
     * )
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getBillingPaymentsAction()
    {
        if ($this->getUser()->getType() !== User::TYPE_ADVERTISER) {
            throw new AccessDeniedHttpException;
        }

        $dates = $this->getRequestedDates();

        $paymentRepo = $this->getDoctrine()->getRepository('VifeedPaymentBundle:Order');
        $payments = $paymentRepo->getPaymentStats($this->getUser(), $dates['date_from'], $dates['date_to']);

        $data = ['payments' => [], 'total' => 0];

        foreach ($payments as $payment) {
            /** @var Order $order */
            $order = $payment[0];
            $data['payments'][] = [
                  'paymentSystemName' => $payment['paymentSystemName'],
                  'date'              => $order->getCreatedAt(),
                  'amount'            => $order->getAmount(),
            ];
            $data['total'] += $order->getAmount();
        }

        $view = new View($data);

        return $this->handleView($view);
    }

    /**
     * статистика по всем площадкам
     *
     * @ApiDoc(
     *     section="Billing statistics API",
     *     resource=true,
     *     parameters={
     *       {"name"="date_from", "dataType"="date", "format"="YYYY-MM-DD", "required"=true},
     *       {"name"="date_to", "dataType"="date", "format"="YYYY-MM-DD", "required"=true}
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Returned when date_from or date_to is not correct",
     *         403="Returned when the user is not authorized to use this method"
     *     }
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getBillingEarningsAction()
    {
        if ($this->getUser()->getType() !== User::TYPE_PUBLISHER) {
            throw new AccessDeniedHttpException;
        }

        $dates = $this->getRequestedDates();

        $platformRepo = $this->getDoctrine()->getRepository('VifeedPlatformBundle:Platform');
        $paymentRepo = $this->getDoctrine()->getRepository('VifeedPaymentBundle:VideoViewPayment');

        $platforms = $platformRepo->findByUserIndexed($this->getUser(), true);

        $data = ['platforms' => [], 'total' => 0];

        if ($platforms) {
            $stats = $paymentRepo->getPlatformStats($platforms, $dates['date_from'], $dates['date_to']);

            foreach ($stats as $row) {
                $data['platforms'][] = [
                      'id'     => $row['platform_id'],
                      'name'   => $platforms[$row['platform_id']]->getName(),
                      'views'  => $row['views'],
                      'earned' => $row['earned']
                ];
                $data['total'] += $row['earned'];
            }
        }

        $view = new View($data);

        return $this->handleView($view);
    }

    /**
     * статистика по дням по площадке
     *
     * @param Platform $platform id площадки
     *
     * @Rest\Get("billing/earnings/{platform_id}", requirements={"platform_id"="\d+"})
     * @ParamConverter("platform", class="VifeedPlatformBundle:Platform",
     *                  options={"id" = "platform_id", "repository_method" = "findWithoutFilter"})
     * @ApiDoc(
     *     section="Billing statistics API",
     *     requirements={
     *       {"name"="platform_id", "dataType"="integer", "requirement"="\d+", "description"="id площадки"}
     *     },
     *     parameters={
     *       {"name"="date_from", "dataType"="date", "format"="YYYY-MM-DD", "required"=true},
     *       {"name"="date_to", "dataType"="date", "format"="YYYY-MM-DD", "required"=true}
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Returned when date_from or date_to is not correct",
     *         403="Returned when the user is not authorized to use this method",
     *         404="Returned when platform is not found"
     *     }
     * )
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getBillingEarningsByPlatformAction(Platform $platform)
    {
        if ($this->getUser()->getType() !== User::TYPE_PUBLISHER) {
            throw new AccessDeniedHttpException;
        }

        if ($this->getUser() !== $platform->getUser()) {
            throw new AccessDeniedHttpException;
        }

        $dates = $this->getRequestedDates();

        $data = ['stats' => [], 'total' => 0];

        $paymentRepo = $this->getDoctrine()->getRepository('VifeedPaymentBundle:VideoViewPayment');
        $stats = $paymentRepo->getPlatformStatsByDay($platform, $dates['date_from'], $dates['date_to']);

        foreach ($stats as $row) {
            $data['total'] += $row['earned'];
        }

        $data['stats'] = $stats;

        $view = new View($data);

        return $this->handleView($view);
    }

    /**
     * вывод средств паблишера
     *
     * @ApiDoc(
     *     section="Billing statistics API",
     *     resource=true,
     *     parameters={
     *       {"name"="date_from", "dataType"="date", "format"="YYYY-MM-DD", "required"=true},
     *       {"name"="date_to", "dataType"="date", "format"="YYYY-MM-DD", "required"=true}
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Returned when date_from or date_to is not correct",
     *         403="Returned when the user is not authorized to use this method"
     *     }
     * )
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getBillingWithdrawalsAction()
    {
        if ($this->getUser()->getType() !== User::TYPE_PUBLISHER) {
            throw new AccessDeniedHttpException;
        }

        $dates = $this->getRequestedDates();

        $repo = $this->getDoctrine()->getRepository('VifeedPaymentBundle:Withdrawal');
        $withdrawals = $repo->getWithdrawalStats($this->getUser(), $dates['date_from'], $dates['date_to']);

        $data = ['withdrawals' => [], 'total' => 0];

        foreach ($withdrawals as $row) {
            /** @var Withdrawal $withdrawal */
            $withdrawal = $row[0];
            $data['withdrawals'][] = [
                  'type'   => $row['type'],
                  'date'   => $withdrawal->getCreatedAt(),
                  'amount' => $withdrawal->getAmount(),
                  'status' => $withdrawal->getStatus()
            ];
            if ($withdrawal->getStatus() == Withdrawal::STATUS_OK) {
                $data['total'] += $withdrawal->getAmount();
            }
        }

        $view = new View($data);

        return $this->handleView($view);
    }


}