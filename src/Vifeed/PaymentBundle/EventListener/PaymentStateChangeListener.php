<?php

namespace Vifeed\PaymentBundle\EventListener;

use Doctrine\ORM\EntityManager;
use JMS\Payment\CoreBundle\Entity\Payment;
use JMS\Payment\CoreBundle\Entity\PaymentInstruction;
use JMS\Payment\CoreBundle\PluginController\Event\Events;
use JMS\Payment\CoreBundle\PluginController\Event\PaymentStateChangeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Vifeed\CampaignBundle\Manager\CampaignManager;
use Vifeed\PaymentBundle\Entity\Order;
use Vifeed\UserBundle\Manager\UserNotificationManager;
use Vifeed\UserBundle\NotificationEvent\OrderPaidEvent;

/**
 * Class PaymentStateChangeListener
 *
 * @package Vifeed\PaymentBundle\EventListener
 */
class PaymentStateChangeListener implements EventSubscriberInterface
{
    private $em;
    private $campaignManager;
    private $notificationManager;

    /**
     * @param \Doctrine\ORM\EntityManager                    $em
     * @param \Vifeed\CampaignBundle\Manager\CampaignManager $campaignManager
     * @param UserNotificationManager                        $notificationManager
     */
    public function __construct(EntityManager $em, CampaignManager $campaignManager, UserNotificationManager $notificationManager)
    {
        $this->em = $em;
        $this->campaignManager = $campaignManager;
        $this->notificationManager = $notificationManager;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
              Events::PAYMENT_STATE_CHANGE => 'onPaymentStateChange'
        );
    }

    /**
     * @param PaymentStateChangeEvent $event
     */
    public function onPaymentStateChange(PaymentStateChangeEvent $event)
    {
        if ($event->getNewState() == Payment::STATE_DEPOSITED) {
            /** @var PaymentInstruction $instruction */
            $instruction = $event->getPaymentInstruction();

            if ($instruction->getAmount() == $instruction->getApprovedAmount()) {
                /** @var Order $order */
                $order = $this->em->getRepository('\Vifeed\PaymentBundle\Entity\Order')->findOneBy(
                                  array(
                                        'paymentInstruction' => $instruction->getId()
                                  )
                );

                $this->em->transactional(function(EntityManager $em) use ($order, $instruction) {
                    $em->getRepository('VifeedUserBundle:User')->updateBalance($order->getUser(), $instruction->getAmount());
                    $order->setStatus(Order::STATUS_PAID);
                    $this->em->persist($order);
                });


                $campaignRepo = $this->em->getRepository('VifeedCampaignBundle:Campaign');
                $campaigns = $campaignRepo->findByUser($order->getUser());

                foreach ($campaigns as $campaign) {
                    $this->campaignManager->checkUpdateStatusAwaiting($campaign);
                }

                $this->em->flush();

                $this->notificationManager->notify($order->getUser(), new OrderPaidEvent(), ['order' => $order]);
            }
        }
    }

}
 