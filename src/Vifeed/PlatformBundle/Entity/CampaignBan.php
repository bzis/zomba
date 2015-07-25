<?php


namespace Vifeed\PlatformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Vifeed\CampaignBundle\Entity\Campaign;


/**
 * Class CampaignBan
 *
 * @ORM\Table(name="campaign_ban")
 * @ORM\Entity(repositoryClass="Vifeed\PlatformBundle\Repository\CampaignBanRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @package Vifeed\PlatformBundle\Entity
 */
class CampaignBan
{

    /**
     * @var Platform
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Vifeed\PlatformBundle\Entity\Platform")
     * @ORM\JoinColumn(name="platform_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $platform;

    /**
     * @var Campaign
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Vifeed\CampaignBundle\Entity\Campaign")
     * @ORM\JoinColumn(name="campaign_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $campaign;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @param Platform $platform
     * @param Campaign $campaign
     */
    public function __construct(Platform $platform, Campaign $campaign)
    {
        $this->platform = $platform;
        $this->campaign = $campaign;
    }


    /**
     * PrePersist
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @param Campaign $campaign
     *
     * @return $this
     */
    public function setCampaign(Campaign $campaign)
    {
        $this->campaign = $campaign;

        return $this;
    }

    /**
     * @return Campaign
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param Platform $platform
     *
     * @return $this
     */
    public function setPlatform(Platform $platform)
    {
        $this->platform = $platform;

        return $this;
    }

    /**
     * @return Platform
     */
    public function getPlatform()
    {
        return $this->platform;
    }


}