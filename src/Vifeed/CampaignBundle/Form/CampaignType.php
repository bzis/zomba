<?php

namespace Vifeed\CampaignBundle\Form;

use DoctrineExtensions\Taggable\TagManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\UserBundle\Entity\User;

/**
 * Class CampaignType
 *
 * @package Vifeed\CampaignBundle\Form
 */
class CampaignType extends AbstractType
{
    private $user;
    private $tagManager;

    public function __construct(User $user, TagManager $tagManager)
    {
        $this->user = $user;
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
              ->add('gender')
              ->add('hash')
              ->add('generalBudget')
              ->add('dailyBudget')
              ->add('bid')
              ->add('countries')
              ->add('tags', 'text', ['mapped' => false])
              ->add('ageRanges');


        $user = $this->user;
        $tagManager = $this->tagManager;

        $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                      function (FormEvent $event) {
                          if (!($event->getData() && $event->getData()->getId())) {
                              $event->getForm()->add('statistics', 'text', ['mapped' => false]);
                          }
                      }
        );

        $builder->addEventListener(
                FormEvents::SUBMIT,
                      function (FormEvent $event) use ($user, $tagManager) {
                          $form = $event->getForm();
                          $formTags = $form['tags']->getData();
                          /** @var Campaign $campaign */
                          $campaign = $event->getData();
                          if (!empty($formTags) && !is_string($formTags)) {
                              $error = new FormError('неправильный формат тегов');
                              $form['tags']->addError($error);
                          } else {
                              $tagNames = $tagManager->splitTagNames($form['tags']->getData());
                              $tags = $tagManager->loadOrCreateTags($tagNames);
                              $tagManager->replaceTags($tags, $campaign);
                          }

                          if ($campaign->getId() === null) {
                              $campaign->setUser($user);
                              $youtubeData = $form['statistics']->getData();
                              if ($youtubeData) {
                                  foreach ($youtubeData as $key => $value) {
                                      $campaign->setYoutubeData($key, $value);
                                  }
                              }
                          }
                          $event->setData($campaign);
                      }
        );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
                 array(
                       'data_class'        => 'Vifeed\CampaignBundle\Entity\Campaign',
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
