<?php

namespace Vifeed\CampaignBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\CampaignBundle\Manager\CampaignManager;
use zis\DaemonBundle\Classes\UniqueProcessTrait;

/**
 * Class CheckCampaignStatusCommand
 *
 * @package Vifeed\CampaignBundle\Command
 *
 * @deprecated с 24.09.2014. Теперь это проверяется в момент оплаты просмотра
 */
class CheckCampaignStatusCommand extends ContainerAwareCommand
{
    use UniqueProcessTrait;

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
              ->setName('vifeed:campaign:check-status')
              ->setDescription('Апдейтит статусы кампаний');
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setPidFile($this->getContainer()->getParameter('daemon.pid_file_location').'/check-campaign-status.pid');
        if (!$this->isDaemonActive()) {
            $this->putPidFile();
        } else {
            throw new \Exception("Демон уже запущен");
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /** @var CampaignManager $manager */
        $manager = $this->getContainer()->get('vifeed.campaign.manager');

        $campaignRepo = $em->getRepository('VifeedCampaignBundle:Campaign');

        $campaigns = $campaignRepo->createQueryBuilder('c')
              ->select('c', 'u')
              ->innerJoin('c.user', 'u')
              ->where('c.status = :status')
              ->setParameter('status', Campaign::STATUS_ON)
              ->getQuery()->getResult();

        foreach ($campaigns as $campaign) {
            $manager->checkUpdateStatusOn($campaign);
        }

        $em->flush();

        $this->unlinkPidFile();
    }

}
