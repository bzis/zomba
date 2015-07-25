<?php

namespace Vifeed\FrontendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;

/**
 * Class FeedbackType
 *
 * @package Vifeed\FrontendBundle\Form
 */
class FeedbackType extends AbstractType
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
                    ]
              ])
              ->add('phone', 'text', [
                    'constraints' => [
                          new Constraints\Length(['min' => 9])
                    ]
              ])
              ->add('email', 'email', [
                    'constraints' => [
                          new Constraints\NotBlank(),
                          new Constraints\Email()
                    ]
              ])
              ->add('message', 'textarea', [
                    'constraints' => [
                          new Constraints\NotBlank(),
                    ]
              ])
        ;
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
        return 'feedback';
    }
} 