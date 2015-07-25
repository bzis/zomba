<?php

namespace Vifeed\PaymentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Vifeed\PaymentBundle\Entity\Wallet;

class WalletType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * todo: ограничить виды кошельков
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
              ->add('type', 'choice', ['choices' => Wallet::getTypes()])
              ->add('number');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                 'data_class'        => 'Vifeed\PaymentBundle\Entity\Wallet',
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
        return 'wallet';
    }

}
