<?php

namespace Vifeed\VideoViewBundle\Command;

use Doctrine\DBAL\LockMode;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\PaymentBundle\Entity\Withdrawal;
use Vifeed\VideoViewBundle\Entity\VideoView;

/**
 * Class GenerateVideoViewCommand
 *
 * @package Vifeed\PlatformBundle\Command
 */
class GenerateWithdrawalCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
              ->setName('vifeed:video-view:generate-withdrawal')
              ->setDescription('Генерит снятия')
              ->addArgument('user', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'пользователи')
            ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userIds = $input->getArgument('user');

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        $userRepo = $em->getRepository('VifeedUserBundle:User');
        $users = $userRepo->findBy(
                        ['id' => $userIds] //, 'status' => Campaign::STATUS_ON
        );

        foreach ($users as $user) {
            $wallet = $em->getRepository('VifeedPaymentBundle:Wallet')->findOneBy(['user' => $user]);
            for ($i = 0; $i < 500; $i++) {
                $em->beginTransaction();
                $em->lock($user, LockMode::PESSIMISTIC_WRITE);
                try {
                    $withdrawal = new Withdrawal();
                    $withdrawal->setUser($user)
                                ->setAmount(20)
                                ->setWallet($wallet)
                                ->setStatus(Withdrawal::STATUS_CREATED);
                    $em->persist($withdrawal);
                    $userRepo->updateBalance($user, -$withdrawal->getAmount());
                    $em->flush();
                    $em->commit();
                } catch (\Exception $e) {
                    $em->rollback();
                    $em->close();
                    throw $e;
                }
                usleep(mt_rand(500, 2000));
            }
        }
    }
}
 