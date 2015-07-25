<?php


namespace Vifeed\PlatformBundle\Manager;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\UnexpectedResultException;
use Hashids\Hashids;
use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\PlatformBundle\Entity\CampaignBan;
use Vifeed\PlatformBundle\Entity\Platform;
use JMS\DiExtraBundle\Annotation as DI;
use Vifeed\TagBundle\Manager\TagManager;

/**
 * Class PlatformManager
 *
 * @package Vifeed\PlatformBundle\Manager
 */
class PlatformManager
{
    /** @var EntityManager */
    private $em;

    /** @var Hashids */
    private $hashids;

    /** @var TagManager */
    private $tagManager;

    /**
     * @param EntityManager                        $em
     * @param \Hashids\Hashids                     $hashids
     * @param \Vifeed\TagBundle\Manager\TagManager $tagManager
     */
    public function __construct(EntityManager $em, Hashids $hashids, TagManager $tagManager)
    {
        $this->em = $em;
        $this->hashids = $hashids;
        $this->tagManager = $tagManager;
    }

    /**
     * сохранение площадки
     *
     * @param Platform $platform
     */
    public function save(Platform $platform)
    {
        $isNew = $platform->getId() ? false : true;
        $this->em->persist($platform);
        $this->em->flush($platform);

        if ($isNew) {
            $platform->setHashId($this->hashids->encode($platform->getId()));
        }

        $platform->updateUpdatedAt();

        $this->tagManager->saveTagging($platform);

        $this->em->flush();
    }

    /**
     * Бан кампании паблишером
     *
     * @param Platform $platform
     * @param Campaign $campaign
     */
    public function banCampaign(Platform $platform, Campaign $campaign)
    {
        $ban = new CampaignBan($platform, $campaign);
        try {
            $this->em->persist($ban);
            $this->em->flush();
        } catch (DBALException $e) {
            throw new UnexpectedResultException();
        }
    }

    /**
     * Разбан кампании паблишером
     *
     * @param Platform $platform
     * @param Campaign $campaign
     * @param bool     $flush
     */
    public function unbanCampaign(Platform $platform, Campaign $campaign, $flush = true)
    {
        $campaignBanRepo = $this->em->getRepository('VifeedPlatformBundle:CampaignBan');
        $ban = $campaignBanRepo->findOneBy(
                               [
                                     'platform' => $platform,
                                     'campaign' => $campaign
                               ]
        );
        if ($ban === null) {
            throw new EntityNotFoundException();
        }

        $this->em->remove($ban);

        if ($flush) {
            $this->em->flush();
        }
    }

} 