<?php

namespace Vifeed\CampaignBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vifeed\CampaignBundle\Entity\Campaign;
use zis\DaemonBundle\Classes\UniqueProcessTrait;

class GetSocialStatsCommand extends ContainerAwareCommand
{
    use UniqueProcessTrait;

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
              ->setName('vifeed:campaign:social-stats')
              ->setDescription('Апдейтит у кампаний статистику с социальных сетей');
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setPidFile($this->getContainer()->getParameter('daemon.pid_file_location') . '/campaign-social-stats.pid');
        if (!$this->isDaemonActive()) {
            $this->putPidFile();
        } else {
            throw new \Exception("Демон уже запущен");
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $router = $this->getContainer()->get('router');
        $facebook = $this->getContainer()->get('vifeed.social.facebook_api_provider');
        $vk = $this->getContainer()->get('vifeed.social.vk_api_provider');
        $google = $this->getContainer()->get('vifeed.social.gplus_api_provider');

        $promoDomain = $this->getContainer()->getParameter('promo_domain');
        $campaignRepo = $em->getRepository('VifeedCampaignBundle:Campaign');
        $videoViewRepo = $em->getRepository('VifeedVideoViewBundle:VideoView');

        /** @var Campaign[] $campaigns */
        $campaigns = $campaignRepo->getIndexedById($campaignRepo->getActiveCampaigns());
        $campaignPlatforms = $videoViewRepo->getCampaignPlatforms($campaigns);

        $urls = [];
        $campaignStats = $this->getEmptyStatsArray();
        $prevCampaignId = $campaignPlatforms[0]['campaignId'];

        if (!$campaignPlatforms) {
            return;
        }

        foreach ($campaignPlatforms as $el) {
            $campaignId = $el['campaignId'];

            if (!is_object($campaigns[$campaignId])) {
                $this->getContainer()->get('logger')->warning('непонятная хрень, кампания ' . $campaignId . ' ' . $campaigns[$campaignId]);
                continue;
            }

            if ($prevCampaignId != $campaignId) {
                $stats = $facebook->getUrlStats($urls);

                foreach ($stats as $stat) {
                    $campaignStats['fbLikes'] += $stat['like_count'];
                    $campaignStats['fbComments'] += $stat['comment_count'];
                    $campaignStats['fbShares'] += $stat['share_count'];
                }
                $campaigns[$campaignId]->setSocialData('fbLikes', $campaignStats['fbLikes'])
                                       ->setSocialData('fbComments', $campaignStats['fbComments'])
                                       ->setSocialData('fbShares', $campaignStats['fbShares'])
                                       ->setSocialData('vkLikes', $campaignStats['vkLikes'])
                                       ->setSocialData('vkShares', $campaignStats['vkShares'])
                                       ->setSocialData('gplusShares', $campaignStats['gplusShares'])
                                       ->setSocialData('linkedPlatforms', $campaignStats['linkedPlatforms'])
                                       ->setSocialData('updatedAt', date('Y-m-d H:i:s'));

                $em->persist($campaigns[$campaignId]);

                $urls = [];
                $campaignStats = $this->getEmptyStatsArray();

            }
            $url = $router->generate('vifeed_video_promo_homepage', [
                  'domain'       => $promoDomain,
                  'campaignHash' => $campaigns[$campaignId]->getHashId(),
                  'platformHash' => $el['platformHash']
            ], true);
            $campaignStats['vkLikes'] += $vk->getLikeCount($url);
            $campaignStats['vkShares'] += $vk->getLikeCount($url, true);
            $campaignStats['gplusShares'] += $google->getSharesCount($url);
            $campaignStats['linkedPlatforms'] += 1;

            $urls[] = $url;
            $prevCampaignId = $campaignId;
        }

        $em->flush();
    }

    /**
     * @return array
     */
    private function getEmptyStatsArray()
    {
        return [
              'vkLikes'         => 0,
              'vkShares'        => 0,
              'fbLikes'         => 0,
              'fbComments'      => 0,
              'fbShares'        => 0,
              'gplusShares'     => 0,
              'linkedPlatforms' => 0,
        ];
    }
}
