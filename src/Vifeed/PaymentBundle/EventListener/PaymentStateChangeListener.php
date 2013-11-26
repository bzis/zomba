<?php

namespace Vifeed\PaymentBundle\EventListener;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Doctrine\UserManager;
use JMS\Payment\CoreBundle\Entity\Payment;
use JMS\Payment\CoreBundle\Entity\PaymentInstruction;
use JMS\Payment\CoreBundle\PluginController\Event\Events;
use JMS\Payment\CoreBundle\PluginController\Event\PaymentStateChangeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Vifeed\PaymentBundle\Entity\Order;

/**
 * Class PaymentStateChangeListener
 *
 * @package Vifeed\PaymentBundle\EventListener
 */
class PaymentStateChangeListener implements EventSubscriberInterface
{
    private $em;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
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
                /** @var Order[] $order */
                $order = $this->em->getRepository('\Vifeed\PaymentBundle\Entity\Order')->findBy(
                    array(
                         'paymentInstruction' => $instruction->getId()
                    )
                );
                $user = $order[0]->getUser();

                $user->updateBalance($instruction->getAmount());
                $this->em->persist($user);
                $this->em->flush($user);
            }
        }
    }


}
 