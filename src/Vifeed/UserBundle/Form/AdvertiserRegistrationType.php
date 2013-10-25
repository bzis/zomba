<?php

namespace Vifeed\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AdvertiserRegistrationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
              ->add('email', 'email');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                 'data_class'        => 'Vifeed\UserBundle\Entity\User',
                 'validation_groups' => array('AdvertiserRegistration'),
                 'intention'         => 'registration',
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'advertiser_registration';
    }
}
