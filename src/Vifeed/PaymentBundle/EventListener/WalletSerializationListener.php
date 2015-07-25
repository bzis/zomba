<?php

namespace Vifeed\PaymentBundle\EventListener;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use PhpOption\Some;
use Vifeed\PaymentBundle\Entity\Wallet;

/**
 * Add data after serialization
 *
 * @package Vifeed\PaymentBundle\EventListener
 */
class WalletSerializationListener implements EventSubscriberInterface
{

    /**
     * {@inheritdoc}
     */
    static public function getSubscribedEvents()
    {
        return array(
              array('event'  => 'serializer.post_serialize',
                    'class'  => 'Vifeed\PaymentBundle\Entity\Wallet',
                    'method' => 'onPostSerialize'),
        );
    }

    /**
     * @param ObjectEvent $event
     */
    public function onPostSerialize(ObjectEvent $event)
    {
        $attribures = $event->getContext()->attributes;

        if ($attribures->containsKey('wallet_data')) {
            /** @var Wallet $wallet */
            $wallet = $event->getObject();
            /** @var Some $walletData */
            $walletData = $attribures->get('wallet_data')->get()[$wallet->getId()];
            $event->getVisitor()->addData('withdrawnAmount', $walletData['withdrawnAmount']);
            $event->getVisitor()->addData('lastOperationDate', $walletData['lastOperationDate']);
        }

    }
}