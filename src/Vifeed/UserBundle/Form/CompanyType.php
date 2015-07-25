<?php

namespace Vifeed\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Vifeed\UserBundle\Entity\Company;
use Vifeed\UserBundle\Entity\User;

class CompanyType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
              ->add('system', 'choice', ['choices' => array_combine(Company::getSystemChoices(), Company::getSystemChoices())])
              ->add('name')
              ->add('contactName')
              ->add('position')
              ->add('address')
              ->add('phone')
              ->add('bankAccount')
              ->add('bic')
              ->add('correspondentAccount')
              ->add('inn')
              ->add('kpp');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
                 [
                       'data_class'      => 'Vifeed\UserBundle\Entity\Company',
                       'csrf_protection' => false
                 ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'company';
    }
}
