<?php

namespace Vifeed\VideoPromoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\DiExtraBundle\Annotation as DI;
use Vifeed\CampaignBundle\Entity\Campaign;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @DI\Inject("doctrine.orm.entity_manager")
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function indexAction($campaignHash, $platformHash, $isNextButton)
    {
        if ($campaignHash === 'next') {
            $campaign = null;
        } else {
            $campaignRepo = $this->em->getRepository('VifeedCampaignBundle:Campaign');
            $campaign = $campaignRepo->findOneBy(['hashId' => $campaignHash]);

            if (!$campaign) {
                throw $this->createNotFoundException('The video is not found');
            }

            if (!$campaign->getUser()->isEnabled()) {
                throw $this->createNotFoundException();
            }
        }

        $platformRepo = $this->em->getRepository('VifeedPlatformBundle:Platform');
        $platform = $platformRepo->findOneBy(['hashId' => $platformHash]);

        if ($platform && !$platform->getUser()->isEnabled()) {
            throw $this->createNotFoundException();
        }

        $videoHash = $campaign ? $campaign->getHash() : null;

        $meta = $this->getMeta($campaign);
        $meta['og:url'] = $this->get('router')->generate('vifeed_video_promo_homepage', [
              'platformHash' => $platformHash,
              'campaignHash' => $campaignHash,
              'domain'       => $this->container->getParameter('promo_domain')
        ]);

        $response = new Response();
        $response->setPublic();
        $response->setMaxAge(60 * 60 * 3);

        return $this->render(
            'VifeedVideoPromoBundle:Default:index.html.twig', [
                'platformHash' => $platformHash,
                'campaignHash' => $campaignHash,
                'campaignTitle' => $campaign ? $campaign->getName() : null,
                'campaignDescription' => $campaign ? $campaign->getDescription() : null,
                'videoHash'    => $videoHash,
                'nextBtn'      => $isNextButton,
                'meta'         => $meta
            ],
            $response
        );
    }

    /**
     * @param Campaign $campaign
     *
     * @return array
     */
    private function getMeta($campaign)
    {
        if (!$campaign) {
            return [];
        }
        $meta = [
              'og:title'           => $campaign->getName(),
              'og:description'     => $campaign->getDescription(),
              'video:duration'     => $campaign->getYoutubeData('duration'),
              'og:image'           => 'http://img.youtube.com/vi/' . $campaign->getHash() . '/sddefault.jpg',
              'ya:ovs:adult'       => 'false',
              'og:type'            => 'video.other',
              'ya:ovs:content_id'  => $campaign->getHash(),
              'ya:ovs:upload_date' => $campaign->getYoutubeData('uploaded'),
              //                      'og:video'           => '', // todo а здесь что?
              //                      'og:video:type'      => '', // todo откуда брать? ютуб?
        ];

        return $meta;
    }
}
