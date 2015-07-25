<?php

namespace Vifeed\PaymentBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vifeed\PaymentBundle\Manager\VideoViewPaymentManager;
use Vifeed\VideoViewBundle\Entity\VideoView;

class PayVideoViewCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
              ->setName('vifeed:payment:pay-views')
              ->setDescription('Инициирует оплату просмотров');
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        /** @var VideoViewPaymentManager $manager */
        $manager = $this->getContainer()->get('vifeed.payment.video_view_payment_manager');

        $repo = $em->getRepository('VifeedVideoViewBundle:VideoView');

        /** @var VideoView[] $views */
        $views = $repo->createQueryBuilder('v')
                      ->where('v.isPaid = false')
                      ->andWhere('v.timestamp > 1412694012')
                      ->andWhere('v.timestamp < 1412708272')
                      ->getQuery()->getResult();
//        $views = $repo->findBy(array('isPaid' => false));

        foreach ($views as $view) {

            $em->refresh($view);
            if ($view->getIsPaid()) {
                continue;
            }

            $manager->reckon($view);
        }
    }
}
 