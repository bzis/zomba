<?php

namespace Vifeed\CampaignBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Vifeed\CampaignBundle\Entity\Campaign;

class CampaignType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
              ->add('name')
              ->add('description')
              ->add('gender')
              ->add('maxBid')
              ->add('hash')
              ->add('budget')
              ->add('budgetType')
              ->add('startAt', 'datetime')
              ->add('endAt', 'datetime')
              ->add('totalViews')
              ->add('bid')
//              ->add('platforms')
              ->add('countries')
              ->add('tags')
              ->add('ageRanges');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                 'data_class' => 'Vifeed\CampaignBundle\Entity\Campaign',
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
        return 'campaign';
    }

}
