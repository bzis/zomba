<?php

namespace Vifeed\PlatformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PlatformVideo
 *
 * @ORM\Table(name="platform_video")
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *      "platform" = "PlatformVideo",
 *      "vk" = "VkVideo"
 * })
 */
class PlatformVideo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="Platform")
     * @ORM\JoinColumn(name="platform_id", referencedColumnName="id")
     */
    private $platform;

    /**
     * @ORM\OneToOne(targetEntity="Vifeed\CampaignBundle\Entity\Campaign")
     * @ORM\JoinColumn(name="campaign_id", referencedColumnName="id")
     */
    private $campaign;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Set platform
     *
     * @param \Vifeed\PlatformBundle\Entity\Platform $platform
     * @return PlatformVideo
     */
    public function setPlatform(\Vifeed\PlatformBundle\Entity\Platform $platform = null)
    {
        $this->platform = $platform;
    
        return $this;
    }

    /**
     * Get platform
     *
     * @return \Vifeed\PlatformBundle\Entity\Platform 
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * Set campaign
     *
     * @param \Vifeed\CampaignBundle\Entity\Campaign $campaign
     * @return PlatformVideo
     */
    public function setCampaign(\Vifeed\CampaignBundle\Entity\Campaign $campaign = null)
    {
        $this->campaign = $campaign;
    
        return $this;
    }

    /**
     * Get campaign
     *
     * @return \Vifeed\CampaignBundle\Entity\Campaign 
     */
    public function getCampaign()
    {
        return $this->campaign;
    }
}