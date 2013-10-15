<?php

namespace Vifeed\PlatformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Vifeed\PlatformBundle\Entity\Platform;

class PlatformType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
              ->add('name')
              ->add('url')
              ->add('description')
              ->add('type')
              ->add('countries')
              ->add('tags');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                 'data_class' => 'Vifeed\PlatformBundle\Entity\Platform',
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
        return 'platform';
    }

}
