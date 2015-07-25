<?php

namespace Vifeed\FrontendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;

/**
 * Class PartnershipType
 *
 * @package Vifeed\FrontendBundle\Form
 */
class PartnershipType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
              ->add('name', 'text', [
                    'constraints' => [
                          new Constraints\NotBlank(),
                          new Constraints\Length(['min' => 5])
                    ]
              ])
              ->add('phone', 'text', [
                    'constraints' => [
                          new Constraints\NotBlank(),
                          new Constraints\Length(['min' => 9])
                    ]
              ])
              ->add('email', 'email', [
                    'constraints' => [
                          new Constraints\NotBlank(),
                          new Constraints\Email()
                    ]
              ]);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
                                     'csrf_protection' => false,
                               ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'partnership';
    }
} 