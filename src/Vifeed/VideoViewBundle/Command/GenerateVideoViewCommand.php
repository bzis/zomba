<?php

namespace Vifeed\VideoViewBundle\Command;

use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\VideoViewBundle\Entity\VideoView;

/**
 * Class GenerateVideoViewCommand
 *
 * @package Vifeed\PlatformBundle\Command
 */
class GenerateVideoViewCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
              ->setName('vifeed:video-view:generate-views')
              ->setDescription('Генерит просмотры')
              ->addArgument('campaigns', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'кампании')
              ->addOption('platforms', null, InputOption::VALUE_OPTIONAL, 'плолщадки', null)
              ->addOption('quantity', null, InputOption::VALUE_OPTIONAL, 'количество показов на площадку', 1);
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rabbit = $this->getContainer()->get('old_sound_rabbit_mq.video_view_producer');

        $campaignIds = $input->getArgument('campaigns');
        $quantity = (int) $input->getOption('quantity');
        $platformIds = $input->getOption('platforms');

        if ($quantity < 1) {
            throw new \LogicException('Неправильное количество');
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        $campaigns = $em->getRepository('VifeedCampaignBundle:Campaign')->findBy(
              ['id' => $campaignIds] //, 'status' => Campaign::STATUS_ON
        );

        $platformRepo = $em->getRepository('VifeedPlatformBundle:Platform');
        if (!$platformIds) {
            $platforms = $em->getRepository('VifeedPlatformBundle:Platform')->findAll();
        } else {
            $platformIds = explode(',', $platformIds);
            $platforms = $em->getRepository('VifeedPlatformBundle:Platform')->findBy(['id' => $platformIds]);
        }
//        $videoViewRepo = $em->getRepository('VifeedVideoViewBundle:VideoView');

        foreach ($campaigns as $campaign) {

            $key = array_rand($platforms);
            $platform = $platforms[$key];

            for ($i = 1; $i <= $quantity; $i++) {
                $query = $em->getConnection()
                            ->query('select country_id, city_id from video_views
                      where city_id is not null and id between 120000 and 260000 order by rand() limit 1');
                $geo = $query->fetch();
                $country = $em->getReference('VifeedGeoBundle:Country', $geo['country_id']);
                $city = $em->getReference('VifeedGeoBundle:City', $geo['city_id']);

                $curTime = mt_rand(5, 150);
                $view = new VideoView();
                $view->setCampaign($campaign)
                     ->setPlatform($platform)
                     ->setCountry($country)
                     ->setCity($city)
                     ->setIp(mt_rand(16777216, 3758096383))
                     ->setCurrentTime($curTime)
                     ->setTimestamp(mt_rand(time() - 20000, time()))
                     ->setTrackNumber($curTime - mt_rand(1, $curTime-1))
                     ->setViewerId(md5(mt_rand()))
                ;

                $em->persist($view);
                $em->flush();

                $rabbit->publish($view->getId());

                usleep(mt_rand(1, 1000));

                if ($i % 100 == 0) {
                    echo $i . "\n";
                }
            }
        }
    }
}
 