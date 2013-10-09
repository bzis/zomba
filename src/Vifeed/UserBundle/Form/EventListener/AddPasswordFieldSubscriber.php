<?php

namespace Vifeed\UserBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Vifeed\UserBundle\Entity\User;

class AddPasswordFieldSubscriber implements EventSubscriberInterface
{

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
        // Tells the dispatcher that you want to listen on the form.pre_set_data
        // event and that the preSetData method should be called.
//        return array(FormEvents::PRE_SET_DATA => 'preSetData');
        return array(FormEvents::SUBMIT => 'preSetData');
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();
        /** @var User $user */
        $user = $event->getData();

        if ($user && ($user->getType() == User::TYPE_PUBLISHER)) {
            $form->add('plainPassword', 'repeated', array(
                                                         'required' => true,
                                                         'type'            => 'password',
                                                         'invalid_message' => 'Пароли должны совпадать!'
                                                    ));
        }
    }
}
