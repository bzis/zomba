<?php

namespace Vifeed\PaymentBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\PaymentBundle\Manager\VideoViewPaymentManager;
use Vifeed\VideoViewBundle\Entity\VideoView;

class TestCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
              ->setName('vifeed:test')
              ->setDescription('Инициирует дооплату просмотров');
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $paymentManager = $this->getContainer()->get('vifeed.payment.video_view_payment_manager');
        $repo = $em->getRepository('VifeedVideoViewBundle:VideoView');
        $view = $repo->findOneBy(['id' => 13695, 'isPaid' => false]);
        if ($view) {
            $result = $paymentManager->reckon($view);
            // если транзакция неудачная, возвращаем таск в очередь для повторного выполнения

        }
    }
}
 