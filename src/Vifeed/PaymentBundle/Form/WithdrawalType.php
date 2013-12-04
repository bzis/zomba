<?php

namespace Vifeed\PaymentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WithdrawalType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
              ->add('wallet')
              ->add('amount');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                 'data_class' => 'Vifeed\PaymentBundle\Entity\Withdrawal',
                 'csrf_protection'   => false,
                 'validation_groups' => array('default'),
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'withdrawal';
    }

}
