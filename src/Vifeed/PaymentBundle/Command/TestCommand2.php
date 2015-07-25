<?php

namespace Vifeed\PaymentBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\PaymentBundle\Manager\VideoViewPaymentManager;
use Vifeed\UserBundle\Entity\User;
use Vifeed\VideoViewBundle\Entity\VideoView;

class TestCommand2 extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
              ->setName('vifeed:payment:test2')
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
        $repo = $em->getRepository('VifeedUserBundle:User');
        $users = $repo->findBy(['enabled' => false, 'type' => User::TYPE_PUBLISHER]);
        foreach ($users as $user) {
            $refund = $paymentManager->refundAllFromPublisher($user);
            var_dump($user->getEmail() . ' - ' . $refund['charged']);
        }

    }
}
 