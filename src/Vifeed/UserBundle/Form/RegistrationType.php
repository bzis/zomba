<?php

namespace Vifeed\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
