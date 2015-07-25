<?php

namespace Vifeed\PaymentBundle\Manager;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\CampaignBundle\Manager\CampaignManager;
use Vifeed\PaymentBundle\Entity\VideoViewPayment;
use Vifeed\UserBundle\Entity\User;
use Vifeed\VideoViewBundle\Entity\VideoView;

/**
 * Class VideoViewPaymentManager
 *
 * @package Vifeed\PaymentBundle\Manager
 */
class VideoViewPaymentManager
{

    /** @var EntityManager */
    private $em;
    /** @var CampaignManager */
    private $campaignManager;
    private $comission;
    private $minViewLength;
    private $shortVideoDuration;
    private $ipFilterConstants;

    /**
     * @param EntityManager   $em
     * @param CampaignManager $campaignManager
     * @param array           $constants
     */
    public function __construct(EntityManager $em, CampaignManager $campaignManager, $constants, $ipFilter)
    {
        $this->em = $em;
        $this->campaignManager = $campaignManager;
        $this->comission = $constants['comission'];
        $this->minViewLength = $constants['min_view_time'];
        $this->shortVideoDuration = $constants['short_video_duration'];
        $this->ipFilterConstants = $ipFilter;
    }

    /**
     * обработка показа
     *
     * @param VideoView $videoView
     * @param bool      $skipStatusCheck
     *
     * @return boolean false, если транзакция не прошла, в остальных случаях true
     */
    public function reckon(VideoView $videoView, $skipStatusCheck = false)
    {
        $videoViewRepo = $this->em->getRepository('VifeedVideoViewBundle:VideoView');
        $userRepo = $this->em->getRepository('VifeedUserBundle:User');
        $campaignRepo = $this->em->getRepository('VifeedCampaignBundle:Campaign');

        $publisher = $videoView->getPlatform()->getUser();
        $this->em->refresh($publisher); // обновляем энтити. Доктрина может брать его из кеша

        if (!$publisher->isEnabled()) {
            // паблишер забанен. Ничего не делаем
            return true;
        }

        // пользователь уже просматривал этот ролик
        $countViewsByViewerId = $videoViewRepo->countCampaignViewsByViewerId($videoView);
        if ($countViewsByViewerId['paid_views'] > 0) {
            return true;
        }

        $campaign = $videoView->getCampaign();
        $this->em->refresh($campaign); // обновляем энтити. Доктрина может брать его из кеша

        // перед оплатой обновляем статус по балансу. Если пора выключать - выключаем
        $this->campaignManager->checkUpdateStatusOn($campaign);


        // если просмотр слишком короткий или кампания уже остановлена, инкрементим totalViews и выходим
        if (!$skipStatusCheck && !$this->checkReckonConditions($videoView)) {
            // засчитываем показ только если пользователь ещё не смотрел ролик
            if ($countViewsByViewerId['views'] == 0) {
                // показ участвует в статах
                $videoView->setIsInStats(true);
                $this->em->persist($videoView);
                $campaignRepo->incrementTotalViews($campaign);
            }
            $this->em->flush(); // на случай, если у кампании обновился статус

            return true;
        }

        $this->em->getConnection()->beginTransaction();

        $this->em->lock($publisher, LockMode::PESSIMISTIC_WRITE);
        $price = $campaign->getBid();
        $comissionValue = round($this->comission * $price, 2);
        $toPay = round($price - $comissionValue, 2);

        $payment = new VideoViewPayment();
        $payment
              ->setVideoView($videoView)
              ->setCharged($price)
              ->setComission($comissionValue)
              ->setPaid($toPay);

        $userRepo->updateBalance($publisher, $toPay);

        $campaignRepo->addPaidView($campaign);
        $this->campaignManager->checkUpdateStatusOn($campaign);

        $videoView->setIsPaid(true)
                  ->setIsInStats(true);

        $this->em->persist($publisher);
        $this->em->persist($payment);

        $done = false;

        try {
            $this->em->flush();
            $this->em->getConnection()->commit();

            $this->em->refresh($campaign);
            $this->campaignManager->checkUpdateStatusOn($campaign);
            $this->em->flush(); // на случай, если у кампании обновился статус

            $done = true;
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
            // исключение не бросаем, просто откатываем транзакцию и ставим задачу снова в очередь
        }

        return $done;
    }

    /**
     * рефанд всех показов с площадок заданного паблишера
     *
     * @param User $publisher
     */
    public function refundAllFromPublisher(User $publisher)
    {
        $platforms = $this->em->getRepository('VifeedPlatformBundle:Platform')->findByUserIndexed($publisher, true);

        $chargePublisherSum = 0;
        $payAdvertiserSum = 0;

        if ($platforms) {
            $userRepo = $this->em->getRepository('VifeedUserBundle:User');

            $filter = $this->em->getFilters()->getFilter('softdeleteable');
            $filter->disableForEntity('Vifeed\CampaignBundle\Entity\Campaign');
            $refundGroups = $this->em->getRepository('VifeedPaymentBundle:VideoViewPayment')->createQueryBuilder('p')
                                     ->select('IDENTITY(c.user) user_id, SUM(p.charged) charged, SUM(p.paid) paid, COUNT(v.id) paid_views')
                                     ->innerJoin('p.videoView', 'v')
                                     ->innerJoin('v.campaign', 'c')
                                     ->where('v.platform IN (:platforms)')
                                     ->groupBy('user_id')
                                     ->setParameter('platforms', $platforms)
                                     ->getQuery()->getResult();
            $filter->enableForEntity('Vifeed\CampaignBundle\Entity\Campaign');

            $this->em->beginTransaction();
            try {
                foreach ($refundGroups as $refundGroup) {
                    $advertiser = $this->em->getReference('VifeedUserBundle:User', $refundGroup['user_id']);
                    $userRepo->updateBalance($advertiser, $refundGroup['charged']);

                    $chargePublisherSum += $refundGroup['paid'];
                    $payAdvertiserSum += $refundGroup['charged'];
                }
                $userRepo->updateBalance($publisher, -$chargePublisherSum);

                $this->em->getRepository('VifeedVideoViewBundle:VideoView')->createQueryBuilder('v')
                         ->update('VifeedVideoViewBundle:VideoView', 'v')
                         ->set('v.isPaid', ':isPaid')
                         ->set('v.isInStats', ':isInStats')
                         ->where('v.platform IN (:platforms)')
                         ->setParameters([
                                               'platforms' => $platforms,
                                               'isPaid'    => false,
                                               'isInStats' => false
                                         ])
                         ->getQuery()->execute();

                $this->em->getRepository('VifeedPaymentBundle:VideoViewPayment')->createQueryBuilder('p')
                         ->delete('VifeedPaymentBundle:VideoViewPayment', 'p')
                         ->where('p.videoView IN (select v from VifeedVideoViewBundle:VideoView v where v.platform IN (:platforms))')
                         ->setParameter('platforms', $platforms)
                         ->getQuery()->execute();

                // пересчитываем количество показов и просмотров
                // todo: можно оптимизировать - считать только кампании выбранных юзеров
                $sqlQuery = 'UPDATE campaign c
                     SET c.total_views = (SELECT COUNT(1) FROM video_views v WHERE v.campaign_id = c.id AND v.is_in_stats=1),
                         c.paid_views = (SELECT COUNT(1) FROM video_views v WHERE v.campaign_id = c.id AND v.is_in_stats=1 AND v.is_paid=1)';
                $this->em->getConnection()->executeQuery($sqlQuery);

                $this->em->commit();

            } catch (\Exception $e) {
                $this->em->rollback();
                throw $e;
            }
        }

        return [
              'charged' => $chargePublisherSum,
              'paid'    => $payAdvertiserSum
        ];
    }

    /**
     * @param \Vifeed\VideoViewBundle\Entity\VideoView $videoView
     *
     * @return boolean
     */
    private function isViewTimeEnough(VideoView $videoView)
    {
        if ($videoView->getTrackNumber() >= $this->minViewLength) {
            return true;
        } else {
            $duration = $videoView->getCampaign()->getYoutubeData('duration');
            if ($duration && ($duration <= $this->shortVideoDuration)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Vifeed\VideoViewBundle\Entity\VideoView $videoView
     *
     * @return boolean
     */
    private function isIpClean(VideoView $videoView)
    {
        $n = $this->ipFilterConstants['short_skip_time'];
        $m = $this->ipFilterConstants['long_skip_time'];
        $x = $this->ipFilterConstants['long_skip_views'];
        $timeoutPerCampaign = $this->ipFilterConstants['timeout_per_campaign'];

        $repo = $this->em->getRepository('VifeedVideoViewBundle:VideoView');
        if ($repo->getSameIpViewsCount($videoView, $n) > 0) {
            return false;
        }
        if ($repo->getSameIpViewsCount($videoView, $m) >= $x) {
            return false;
        }
        if ($repo->getSameIpViewsCount($videoView, $timeoutPerCampaign, $videoView->getCampaign()) > 0) {
            return false;
        }

        return true;
    }

    /**
     * @param \Vifeed\VideoViewBundle\Entity\VideoView $videoView
     *
     * @return boolean
     */
    private function checkReckonConditions(VideoView $videoView)
    {
        if (!$this->isViewTimeEnough($videoView)) {
            return false;
        }
        if ($videoView->getCampaign()->getStatus() != Campaign::STATUS_ON) {
            return false;
        }
        if (!$this->isIpClean($videoView)) {
            return false;
        }

        return true;
    }

}
