<?php

namespace Vifeed\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Vifeed\UserBundle\Entity\User;

class ProfileType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
              ->add('first_name')
              ->add('surname')
              ->add('phone')
              ->add('notification', 'choice', ['choices' => ['email', 'sms'], 'multiple' => true, 'by_reference' => false])
              ->add('email', 'email');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
                 [
                       'data_class'        => 'Vifeed\UserBundle\Entity\User',
                       'validation_groups' => array('ApiProfile'),
                       'csrf_protection'   => false
                 ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'profile';
    }
}
