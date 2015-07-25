<?php

namespace Vifeed\PlatformBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class VkPlatformType
 *
 * @package Vifeed\PlatformBundle\Form
 */
class VkPlatformType extends PlatformType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('vkId');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults(['data_class' => 'Vifeed\PlatformBundle\Entity\VkPlatform']);
    }


}
