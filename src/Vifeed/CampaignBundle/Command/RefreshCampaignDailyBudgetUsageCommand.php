<?php

namespace Vifeed\CampaignBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\CampaignBundle\Manager\CampaignManager;

class RefreshCampaignDailyBudgetUsageCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
              ->setName('vifeed:campaign:refresh-daily-budget-usage')
              ->setDescription('Апдейтит статусы кампаний');
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /** @var CampaignManager $manager */
        $manager = $this->getContainer()->get('vifeed.campaign.manager');

        $campaignRepo = $em->getRepository('VifeedCampaignBundle:Campaign');

        /** @var Campaign[] $campaigns */
        $campaigns = $campaignRepo->createQueryBuilder('c')
              ->select('c', 'u')
              ->innerJoin('c.user', 'u')
              ->where('c.status = :status')
              ->andWhere('c.dailyBudget > 0 and c.dailyBudgetUsed > 0')
              ->setParameter('status', Campaign::STATUS_AWAITING)
              ->getQuery()->getResult();


        foreach ($campaigns as $campaign) {
            $campaign->resetDailyBudgetUsed();
            $manager->checkUpdateStatusAwaiting($campaign);
        }


        $campaignRepo->createQueryBuilder('c')
              ->update()
              ->set('c.dailyBudgetUsed', 0)
              ->getQuery()->execute();

        $em->flush();
    }
}