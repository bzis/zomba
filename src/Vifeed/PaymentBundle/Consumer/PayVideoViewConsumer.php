<?php

namespace Vifeed\PaymentBundle\Consumer;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Vifeed\PaymentBundle\Manager\VideoViewPaymentManager;

/**
 * Class PayVideoViewConsumer
 *
 * @package Vifeed\PaymentBundle\Consumer
 */
class PayVideoViewConsumer implements ConsumerInterface
{
    /** @var \Doctrine\ORM\EntityManager */
    protected $em;
    /** @var \Vifeed\PaymentBundle\Manager\VideoViewPaymentManager */
    protected $paymentManager;

    public function __construct(EntityManager $em, VideoViewPaymentManager $paymentManager)
    {
        $this->em = $em;
        $this->paymentManager = $paymentManager;
    }

    public function execute(AMQPMessage $msg)
    {
        $id = $msg->body;

        $repo = $this->em->getRepository('VifeedVideoViewBundle:VideoView');
        $view = $repo->findOneBy(['id' => $id, 'isPaid' => false]);
        if ($view) {
            $result = $this->paymentManager->reckon($view);
            // если транзакция неудачная, возвращаем таск в очередь для повторного выполнения
            if (!$result) {
                return ConsumerInterface::MSG_SINGLE_NACK_REQUEUE;
            }
        }

        return ConsumerInterface::MSG_ACK;
    }

} 