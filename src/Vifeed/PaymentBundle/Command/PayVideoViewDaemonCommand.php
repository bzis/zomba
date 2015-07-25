<?php

namespace Vifeed\PaymentBundle\Command;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\Consumer;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Vifeed\PaymentBundle\Manager\VideoViewPaymentManager;
use Vifeed\SystemBundle\RabbitMQ\ConnectionManager;
use zis\DaemonBundle\Classes\UniqueProcessTrait;
use zis\DaemonBundle\Command\AbstractDaemonizeableCommand;

declare(ticks = 1);

class PayVideoViewDaemonCommand extends AbstractDaemonizeableCommand
{
    use UniqueProcessTrait;

    const QUEUE_NAME = 'video-view-payment';

    /** @var ConnectionManager */
    protected $rabbitConnectionManager;

    protected $defaultChannel;

    /** @var LoggerInterface */
    protected $logger;

    /** @var EntityManager */
    protected $em;

    /** @var VideoViewPaymentManager */
    protected $paymentManager;

    protected $maxProcesses;

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
              ->setName('vifeed:payment:pay-views-daemon')
              ->setDescription('Инициирует оплату просмотров');
    }

    protected function preProcess()
    {
        echo "Running daemon controller " . getmypid() . PHP_EOL;

        $this->setPidFile($this->getContainer()->getParameter('daemon.pid_file_location') . '/pay-views-daemon.pid');
        if (!$this->isDaemonActive()) {
            $this->putPidFile();
        } else {
            throw new \Exception("Демон уже запущен");
        }

        $this->logger = $this->getContainer()->get('logger');
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->rabbitConnectionManager = $this->getContainer()->get('vifeed.rabbitmq.connection_manager');
        $this->paymentManager = $this->getContainer()->get('vifeed.payment.video_view_payment_manager');
        $this->defaultChannel = $this->rabbitConnectionManager->getDefaultConnection()->channel();

        $this->maxProcesses = $this->getContainer()->getParameter('vifeed.payment.max_workers');

        // одного воркера вешаем сразу - пусть всегда работает
        $this->launchJob();
    }

    /**
     * гоняется в цикле, пока не придёт команда остановиться
     */
    protected function process()
    {
        // todo проверить наличие очереди
        $length = $this->defaultChannel->queue_declare(self::QUEUE_NAME, true)[1];
        $jobs = count($this->currentJobs);

//        echo ('Заданий в очереди: ' . $length . ', ' . $jobs . ' воркеров'.PHP_EOL);

        if ($length >= $jobs * 2) {
            // Если уже запущено максимальное количество дочерних процессов, ждем их завершения
            if (count($this->currentJobs) >= $this->maxProcesses) {
                $this->logger->info('WARN: Запущено максимальное количество процессов (' . count($this->currentJobs) . '), в очереди ещё ' . $length . ' задач',
                                    ['paymentDaemon']);
            } else {
                $this->launchJob();
            }
        } elseif ($jobs > 1) {
            $this->killChild(array_values($this->currentJobs)[0]);
        }
        sleep(1);
    }

    /**
     * этот код выполняется дочерним процессом.
     */
    protected function processJob($parameters = [])
    {
        $consumer = clone $this->getContainer()->get('old_sound_rabbit_mq.pay_video_view_consumer');
        try {
            $consumer->consume(0);
        } catch (AMQPTimeoutException $e) {
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function daemonize()
    {
        $this->addSigHandler(SIGTERM, [$this, 'sigHandler']);
        $this->addSigHandler(SIGCHLD, [$this, 'sigHandler']);
    }
}
