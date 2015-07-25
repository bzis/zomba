<?php

namespace Vifeed\PlatformBundle\Form;

use Buzz\Util\Url;
use DoctrineExtensions\Taggable\TagManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Vifeed\PlatformBundle\Entity\Platform;
use Symfony\Component\Validator\Constraints;

/**
 * Class PlatformType
 *
 * @package Vifeed\PlatformBundle\Form
 */
class PlatformType extends AbstractType
{

    private $tagManager;

    public function __construct(TagManager $tagManager)
    {
        $this->tagManager = $tagManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
              ->add('name')
              ->add('description')
              ->add('countries')
              ->add('tags', 'text', ['mapped' => false]);


        $tagManager = $this->tagManager;

        $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                      function (FormEvent $event) {
                          if (!$event->getData() || !$event->getData()->getId()) {
                              $event->getForm()
                                    ->add('url', 'url')
                                    ->add('type', 'text', [
                                                      'mapped'      => false,
                                                      'constraints' => [
                                                            new Constraints\NotBlank(['groups' => ['new']]),
                                                            new Constraints\Choice([
                                                                                         'choices' => Platform::getAvailableTypes(),
                                                                                         'groups'  => ['new'],
                                                                                         'message' => 'Неправильный тип площадки'
                                                                                   ])
                                                      ]
                                                ]
                                    );
                          }
                      }
        );

        $builder->addEventListener(
                FormEvents::SUBMIT,
                      function (FormEvent $event) use ($tagManager) {
                          /** @var Platform $platform */
                          $platform = $event->getData();
                          $form = $event->getForm();

                          $formTags = $form['tags']->getData();
                          if (!empty($formTags) && !is_string($formTags)) {
                              $error = new FormError('неправильный формат тегов');
                              $form['tags']->addError($error);
                          } else {
                              $tagNames = $tagManager->splitTagNames($form['tags']->getData());
                              $tags = $tagManager->loadOrCreateTags($tagNames);
                              $tagManager->replaceTags($tags, $platform);
                          }

                          if (!$platform->getId() && $platform->getUrl()) { // для новой площадки
                              $url = new Url($platform->getUrl());
                              $platform->setUrl(rtrim($url->format('hp'), '//'));

                              if (in_array($url->getHostname(), ['vk.com', 'vkontakte.ru']) !== ($form['type']->getData() == 'vk')) {
                                  $error = new FormError('Тип площадки не соответствует адресу');
                                  $form['url']->addError($error);
                              }
                          }
                      }
        );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
                                     'data_class'        => 'Vifeed\PlatformBundle\Entity\Platform',
                                     'csrf_protection'   => false,
                                     'validation_groups' => function (FormInterface $form) {
                                               $groups = [];
                                               if ($form->getData() && $form->getData()->getId()) {
                                                   $groups[] = 'existent';
                                               } else {
                                                   $groups[] = 'new';
                                               }

                                               if ($this instanceof VkPlatformType) {
                                                   $groups[] = 'vk';
                                               }

                                               return $groups;
                                           }
                               ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'platform';
    }

}
