<?php


namespace Vifeed\CampaignBundle\Manager;

use Doctrine\ORM\EntityManager;
use Hashids\Hashids;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\CampaignBundle\Exception\CampaignStatusException;
use Vifeed\TagBundle\Manager\TagManager;

/**
 * Class CampaignManager
 *
 * @package Vifeed\CampaignBundle\Manager
 */
class CampaignManager
{
    private $delta = 0;

    /** @var EntityManager */
    private $em;

    /** @var Hashids */
    private $hashids;

    /** @var TagManager */
    private $tagManager;

    /**
     * @param EntityManager $em
     * @param Hashids       $hashids
     * @param TagManager    $tagManager
     * @param array         $constants
     */
    public function __construct(EntityManager $em, Hashids $hashids, TagManager $tagManager, $constants)
    {
        $this->em = $em;
        $this->hashids = $hashids;
        $this->tagManager = $tagManager;
        $this->delta = $constants['delta'];
        $this->comission = $constants['comission'];
    }

    /**
     * Сохраняет кампанию и хеширует id
     *
     * @param Campaign $campaign
     */
    public function save(Campaign $campaign)
    {
        $isNew = $campaign->getId() ? false : true;
        $this->em->persist($campaign);
        $this->em->flush($campaign);

        if ($isNew) {
            $campaign->setHashId($this->hashids->encode($campaign->getId()));
        }

        $campaign->updateUpdatedAt();

        $this->tagManager->saveTagging($campaign);

        $this->em->flush();
    }

    /**
     * Проверяет работающие кампании и если нужно, меняет статусы
     *
     * @param Campaign $campaign
     */
    public function checkUpdateStatusOn(Campaign $campaign)
    {
        if ($campaign->getStatus() != Campaign::STATUS_ON) {
            return;
        }

        $minBalance = $this->getMinBalanceByBid($campaign->getBid());

        if ($campaign->getBalance() < $minBalance) { // бюджет кампании закончился

            $campaign->setStatus(Campaign::STATUS_ENDED);
            $this->em->persist($campaign);

        } elseif ($campaign->hasDailyBudgetLimit() && ($campaign->getDailyBudgetRemains() < $minBalance)) {
            // дневной лимит израсходован
            $campaign->setStatus(Campaign::STATUS_AWAITING);
            $this->em->persist($campaign);
        }
    }

    /**
     * @param Campaign $campaign
     * @param bool     $flush
     */
    public function checkUpdateStatusAwaiting(Campaign $campaign, $flush = false)
    {
        if ($campaign->getStatus() != Campaign::STATUS_AWAITING) {
            return;
        }

        if ($this->canTurnOnByMinBalance($campaign) && $this->canTurnOnByDailyLimit($campaign)) {
            $this->turnOn($campaign);
            $this->em->persist($campaign);

            if ($flush) {
                $this->em->flush($campaign);
            }
        }
    }

    /**
     * проверка всех условий для включения кампании и само включение
     *
     * @param Campaign $campaign
     * @param string   $oldStatus
     */
    public function tryTurnStatusOn(Campaign $campaign, $oldStatus)
    {
        if (in_array($oldStatus, [Campaign::STATUS_ARCHIVED, Campaign::STATUS_ENDED]) || $campaign->getStartAt() === null) {
            if ($campaign->getUser()->getBalance() < $campaign->getGeneralBudget()) {
                throw new CampaignStatusException('Недостаточно свободных средств для запуска кампании');
            }

            $this->em->transactional(function (EntityManager $em) use ($campaign) {
                $em->getRepository('VifeedUserBundle:User')->updateBalance($campaign->getUser(), -$campaign->getGeneralBudget());
                $em->getRepository('VifeedCampaignBundle:Campaign')->updateBalance($campaign, $campaign->getGeneralBudget());
                $em->refresh($campaign);
            });
        }

        if (!$this->canTurnOnByMinBalance($campaign)) {
            throw new CampaignStatusException('Недостаточно средств для запуска кампании');
        } elseif (!$this->canTurnOnByDailyLimit($campaign)) {
            throw new CampaignStatusException('Дневной бюджет кампании на сегодня исчерпан');
        }

        // даже если awaiting, тут можно включить, потому что все условия были соблюдены

        $this->turnOn($campaign);
    }

    /**
     * вернуть деньги с баланса кампании на счёт юзера
     *
     * @param Campaign $campaign
     */
    public function transferMoneyBackToUser(Campaign $campaign)
    {
        $this->em->transactional(function (EntityManager $em) use ($campaign) {
            $em->getRepository('VifeedUserBundle:User')->updateBalance($campaign->getUser(), $campaign->getBalance());
            $campaign->setBalance(0);
            $em->persist($campaign);
        });
    }

    /**
     * Проверка, можно ли включить кампанию по дневному лимиту
     *
     * @param Campaign $campaign
     *
     * @return boolean
     */
    public function canTurnOnByDailyLimit($campaign)
    {
        $minBalance = $this->getMinBalanceByBid($campaign->getBid());

        if ($campaign->hasDailyBudgetLimit() ? $campaign->getDailyBudgetRemains() >= $minBalance : true) {
            return true;
        }

        return false;
    }

    /**
     * Проверка, можно ли включить кампанию по общему лимиту
     *
     * @param Campaign $campaign
     *
     * @deprecated фактически, можно использовать только баланс
     *
     * @return boolean
     */
    public function canTurnOnByGeneralLimit($campaign)
    {
        return $this->canTurnOnByMinBalance($campaign);
    }

    /**
     * Проверка, можно ли включить кампанию по минимальному балансу
     *
     * @param Campaign $campaign
     *
     * @return boolean
     */
    public function canTurnOnByMinBalance($campaign)
    {
        $minBalance = $this->getMinBalanceByBid($campaign->getBid());

        if ($campaign->getBalance() >= $minBalance) {
            return true;
        }

        return false;
    }

    /**
     * Кампнию можно выключить только если прошло минимум 2 часа с момента запуска
     *
     * @param Campaign $campaign
     *
     * @return bool|int
     */
    public function canTurnOffByTime(Campaign $campaign)
    {
        if (time() - $campaign->getStartAt()->getTimestamp() < 7200) { // 120 минут
            return ceil((7200 + $campaign->getStartAt()->getTimestamp() - time()) / 60);
        }

        return true;
    }

    /**
     * @param Campaign $campaign
     *
     * @return array
     */
    public function getCampaignParametersForPartner(Campaign $campaign)
    {
        $multiplier = 1 - $this->comission;

        return [
              'bid'           => round($campaign->getBid() * $multiplier, 2),
              'budget'        => round($campaign->getGeneralBudget() * $multiplier, 2),
              'budgetRemains' => round($campaign->getBalance() * $multiplier, 2),
        ];
    }

    /**
     * @param \Vifeed\CampaignBundle\Entity\Campaign $campaign
     */
    private function turnOn(Campaign $campaign)
    {
        $campaign->setStatus(Campaign::STATUS_ON)
                 ->setStartAt(new \DateTime());
    }

    /**
     * @param float $bid
     *
     * @return float
     */
    private function getMinBalanceByBid($bid)
    {
        return round(($this->delta + 1) * $bid, 2);
    }
} 