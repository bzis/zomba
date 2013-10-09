<?php

namespace Vifeed\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Vifeed\UserBundle\Entity\User;
use Vifeed\UserBundle\Form\EventListener\AddPasswordFieldSubscriber;

class RegistrationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
              ->add('email', 'email')
              ->add('type', null, array('required' => true));
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function(FormEvent $event) {
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
                $form->handleRequest();
//                $form->submit();
            });
        /*
        $builder->add('plainPassword', 'repeated', array(
                                                     'type'            => 'password',
                                                     'invalid_message' => 'Пароли должны совпадать!'
                                                ));*/
//        $builder->addEventSubscriber(new AddPasswordFieldSubscriber());
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                 'data_class'        => 'Vifeed\UserBundle\Entity\User',
                 'validation_groups' => array('FastRegistration'),
                 'intention'  => 'registration',
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'registration';
    }
}
