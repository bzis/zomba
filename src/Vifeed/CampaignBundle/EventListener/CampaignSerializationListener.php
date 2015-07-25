<?php

namespace Vifeed\CampaignBundle\EventListener;

use Doctrine\ORM\EntityManager;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\CampaignBundle\Manager\CampaignManager;

/**
 * Add data after serialization
 *
 * @package Vifeed\PlatformBundle\Listener
 */
class CampaignSerializationListener implements EventSubscriberInterface
{

    private $em;
    private $campaignManager;

    public function __construct(EntityManager $em, CampaignManager $campaignManager)
    {
        $this->em = $em;
        $this->campaignManager = $campaignManager;
    }

    /**
     * {@inheritdoc}
     */
    static public function getSubscribedEvents()
    {
        return [
              [
                    'event'  => 'serializer.post_serialize',
                    'class'  => 'Vifeed\CampaignBundle\Entity\Campaign',
                    'method' => 'onPostSerialize'
              ],
              /*[
                    'event'  => 'serializer.pre_serialize',
                    'class'  => 'Vifeed\CampaignBundle\Entity\Campaign',
                    'method' => 'onPreSerialize'
              ],*/
        ];
    }

    /**
     * @param ObjectEvent $event
     */
    public function onPostSerialize(ObjectEvent $event)
    {
        /** @var Campaign $campaign */
        $campaign = $event->getObject();

        $attribures = $event->getContext()->attributes;
        // если кампания в списке забаненных, то добавляет сериализованной кампании свойство "banned: true"
        if ($attribures->containsKey('banned_campaigns')) {
            $bannedCampaigns = $attribures->get('banned_campaigns');
            if (in_array($campaign, $bannedCampaigns->get(), true)) {
                $banned = true;
            } else {
                $banned = false;
            }
            $event->getVisitor()->addData('banned', $banned);
        }

        $groups = $attribures->get('groups')->get();
        if (in_array('default', $groups)) {
            $campaignData = $this->campaignManager->getCampaignParametersForPartner($campaign);
            $event->getVisitor()->addData('bid', $campaignData['bid']);
            $event->getVisitor()->addData('general_budget', $campaignData['budget']);
            $event->getVisitor()->addData('general_budget_remains', $campaignData['budgetRemains']);
        }
    }

    /**
     * @param PreSerializeEvent $event
     * @deprecated с 10.09.2014
     */
    public function onPreSerialize(PreSerializeEvent $event)
    {
        /** @var Campaign $campaign */
        $campaign = $event->getObject();

        $attribures = $event->getContext()->attributes;

        $groups = $attribures->get('groups')->get();

        if (in_array('own_detailed', $groups)) {
            $linkedPlatforms = $this->em->getRepository('VifeedVideoViewBundle:VideoView')->getCampaignPlatformsCount($campaign);
            $campaign->setSocialData('linkedPlatforms', $linkedPlatforms);
        }

    }
}