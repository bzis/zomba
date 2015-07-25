<?php

namespace Vifeed\UserBundle\Form;

use FOS\UserBundle\Form\Type\ResettingFormType as FOSResettingFormType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ResettingFormType extends FOSResettingFormType
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct('Vifeed\UserBundle\Entity\User');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['csrf_protection'   => false,
                                'validation_groups' => ['ApiChangePassword']]);
        parent::setDefaultOptions($resolver);
    }

    public function getName()
    {
        return 'resetting';
    }
}
