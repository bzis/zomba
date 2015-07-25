<?php

namespace Vifeed\PaymentBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\PaymentBundle\Manager\VideoViewPaymentManager;
use Vifeed\VideoViewBundle\Entity\VideoView;

class SurchargeVideoViewCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
              ->setName('vifeed:payment:surcharge-views')
              ->setDescription('Инициирует дооплату просмотров');
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $delta = $this->getContainer()->getParameter('vifeed')['delta'];
        $minViewTime = $this->getContainer()->getParameter('vifeed')['min_view_time'];

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /** @var VideoViewPaymentManager $paymentManager */
        $paymentManager = $this->getContainer()->get('vifeed.payment.video_view_payment_manager');

        /** @var Campaign[] $campaigns */
        $campaigns = $em->createQueryBuilder()
                        ->select('c')
                        ->from('VifeedCampaignBundle:Campaign', 'c')
                        ->where('c.status IN (:statuses)')
                        ->andWhere('c.balance >= c.bid')
                        ->andWhere('(c.dailyBudget = 0) OR (c.dailyBudget - c.dailyBudgetUsed >= c.bid)')
                        ->setParameter('statuses', [Campaign::STATUS_AWAITING, Campaign::STATUS_ENDED])
                        ->getQuery()->getResult();

        foreach ($campaigns as $campaign) {
            $numToPay = floor($campaign->getBalance() / $campaign->getBid());
            if ($campaign->hasDailyBudgetLimit()) {
                $numToPay = min($numToPay, floor($campaign->getDailyBudgetRemains() / $campaign->getBid()));
            }

            if ($numToPay > $delta) {
                $this->getContainer()->get('logger')
                     ->warning('Возможо, ошибка: у кампании ' . $campaign->getId() . ' осталось ' . $numToPay . ' показов к оплате');
                continue;
            }

            /** @var VideoView[] $views */
            $views = $em->createQueryBuilder()
                        ->select('v')
                        ->from('VifeedVideoViewBundle:VideoView', 'v')
                        ->where('v.campaign = :campaign')
                        ->andWhere('v.isPaid = false')
                        ->andWhere('v.trackNumber >= :minViewTime')
                        ->orderBy('v.id', 'asc')
                        ->setParameter('campaign', $campaign)
                        ->setParameter('minViewTime', $minViewTime)
                        ->getQuery()->getResult();

            foreach ($views as $view) {
                $paymentManager->reckon($view, true);
                if ($view->getIsPaid()) {
                    $numToPay--;
                    if ($numToPay == 0) {
                        break;
                    }
                }
            }

        }
    }
}
 