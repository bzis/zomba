<?php

namespace Vifeed\UserBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class ChangePasswordFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
                'currentPassword', 'password', [
                                        'translation_domain' => 'FOSUserBundle',
                                        'mapped'             => false,
                                        'constraints'        => new UserPassword()
                                  ]
        );
        $builder->add(
                'plainPassword', 'repeated', [
                                     'type'            => 'password',
                                     'options'         => ['translation_domain' => 'FOSUserBundle'],
                                     'invalid_message' => 'fos_user.password.mismatch',
                               ]
        );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
                 [
                       'data_class'        => 'Vifeed\UserBundle\Entity\User',
                       'csrf_protection'   => false,
                       'intention'         => 'change_password',
                       'validation_groups' => ['Default', 'ApiChangePassword'],
                 ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'change_password';
    }
}

