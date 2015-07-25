<?php

namespace Vifeed\CampaignBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Vifeed\CampaignBundle\Entity\Campaign;

/**
 * Class CampaignStatusType
 *
 * @package Vifeed\CampaignBundle\Form
 */
class CampaignStatusType extends CampaignType
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
        $builder->add('status');
    }


    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults(['validation_groups' => ['status']]);
    }

}
