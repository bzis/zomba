<?php

namespace Vifeed\CampaignBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class CampaignType
 *
 * @package Vifeed\CampaignBundle\Form
 */
class StartedCampaignType extends CampaignType
{

    public function __construct()
    {
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
              ->add('name')
              ->add('description');

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults(['validation_groups' => ['running']]);
    }

}
