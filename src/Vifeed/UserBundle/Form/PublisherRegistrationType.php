<?php

namespace Vifeed\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PublisherRegistrationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
              ->add('email', 'email')
              ->add(
                  'plainPassword',
                  'repeated',
                  array(
                       'required'        => true,
                       'type'            => 'password',
                       'invalid_message' => 'Пароли должны совпадать!'
                  )
              );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                 'data_class'        => 'Vifeed\UserBundle\Entity\User',
                 'validation_groups' => array('PublisherRegistration'),
                 'intention'         => 'registration',
                 'csrf_protection'   => false
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'publisher_registration';
    }
}
