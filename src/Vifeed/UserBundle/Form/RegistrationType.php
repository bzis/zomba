<?php

namespace Vifeed\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Vifeed\UserBundle\Entity\User;

class RegistrationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
              ->add(
              'type', 'choice', [
                          'choices' => [
                                User::TYPE_PUBLISHER  => User::TYPE_PUBLISHER,
                                User::TYPE_ADVERTISER => User::TYPE_ADVERTISER
                          ]
                    ]
              )
              ->add('email', 'email')
              ->add(
              'plainPassword', 'repeated', [
                                   'required'        => true,
                                   'type'            => 'password',
                                   'invalid_message' => 'Пароли должны совпадать!'
                             ]
              );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
                 [
                       'data_class'        => 'Vifeed\UserBundle\Entity\User',
                       'validation_groups' => array('ApiRegistration'),
                       'csrf_protection'   => false
                 ]
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
