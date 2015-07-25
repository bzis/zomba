<?php

namespace Vifeed\CampaignBundle\Command;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vifeed\CampaignBundle\Entity\Campaign;


class GetYouTubeStatsCommand extends ContainerAwareCommand
{
    const HASHES_PORTION_SIZE = 50;

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
              ->setName('vifeed:campaign:youtube-stats')
              ->setDescription('Апдейтит у кампаний статистику с ютуба');
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $campaignRepo = $em->getRepository('VifeedCampaignBundle:Campaign');

        $campaigns = new ArrayCollection($campaignRepo->getActiveCampaigns());
        $hashes = [];

        foreach ($campaigns as $campaign) {
            /** @var Campaign $campaign */
            $hashes[] = $campaign->getHash();
        }
        $hashes = array_unique($hashes);

        $client = new \Google_Client();
        $client->setDeveloperKey($this->getContainer()->getParameter('google.api.key'));
        $youtube = new \Google_Service_YouTube($client);

        /* Опытным путём выяснилось, что ютуб принимает не больше 50 хешей за раз */
        $hashesPortions = ceil(sizeof($hashes) / self::HASHES_PORTION_SIZE);

        for ($i = 0; $i < $hashesPortions; $i++) {
            $currentHashes = array_slice($hashes, $i * self::HASHES_PORTION_SIZE, self::HASHES_PORTION_SIZE);
            $request = $youtube->videos->listVideos('statistics', ['id' => join(',', $currentHashes)]);
            foreach ($request as $video) {
                /** @var \Google_Service_YouTube_Video $video */
                /** @var \Google_Service_YouTube_VideoStatistics $stats */
                $stats = $video->getStatistics();

                $hash = $video->getId();
                /* не исключается ситуация, что может быть несколько кампаний с одинаковым hash */
                $filteredCampaigns = $campaigns->filter(
                                               function (Campaign $campaign) use ($hash) {
                                                   return $campaign->getHash() == $hash;
                                               }
                );

                foreach ($filteredCampaigns as $campaign) {
                    $campaign->setYoutubeData('views', $stats->getViewCount())
                             ->setYoutubeData('comments', $stats->getCommentCount())
                             ->setYoutubeData('favorites', $stats->getFavoriteCount())
                             ->setYoutubeData('likes', $stats->getLikeCount())
                             ->setYoutubeData('dislikes', $stats->getDislikeCount())
                             ->setYoutubeData('updatedAt', date('Y-m-d H:i:s'));
                    $em->persist($campaign);
                }
            }
        }

        $em->flush();
    }
}