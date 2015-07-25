<?php

namespace Vifeed\VideoViewBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Vifeed\GeoBundle\Entity\City;
use Vifeed\GeoBundle\Entity\Country;

/**
 * VideoView
 *
 * @ORM\Table(
 *            name="video_views",
 *            indexes={
 *                      @ORM\Index(name="ip", columns={"ip"}),
 *                      @ORM\Index(name="is_paid_viewer", columns={"viewer_id", "campaign_id", "is_paid", "timestamp", "is_in_stats"})
 *            })
 *           )
 * @ORM\Entity(repositoryClass="Vifeed\VideoViewBundle\Repository\VideoViewRepository")
 */
class VideoView
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
     * @ORM\ManyToOne(targetEntity="Vifeed\PlatformBundle\Entity\Platform")
     * @ORM\JoinColumn(name="platform_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $platform;

    /**
     * @ORM\ManyToOne(targetEntity="Vifeed\CampaignBundle\Entity\Campaign")
     * @ORM\JoinColumn(name="campaign_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $campaign;

    /**
     * @ORM\Column(name="`current_time`", type="smallint")
     */
    private $currentTime;

    /**
     * @ORM\Column(name="timestamp", type="integer")
     */
    private $timestamp;

    /**
     * @ORM\Column(name="fingerprint", type="integer", nullable=true)
     */
    private $fingerprint;

    /**
     * @ORM\Column(name="track_number", type="smallint")
     */
    private $trackNumber;

    /**
     * @ORM\Column(name="ip", type="bigint", nullable=true)
     */
    private $ip;

    /**
     * @ORM\ManyToOne(targetEntity="Vifeed\GeoBundle\Entity\Country")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $country;

    /**
     * @ORM\ManyToOne(targetEntity="Vifeed\GeoBundle\Entity\City")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $city;

    /**
     * @ORM\Column(name="is_paid", type="boolean")
     */
    private $isPaid = false;

    /**
     * @ORM\Column(name="viewer_id", type="string", nullable=false, columnDefinition="CHAR(43) NOT NULL DEFAULT ''")
     */
    private $viewerId;

    /**
     * @ORM\Column(name="is_in_stats", type="boolean")
     */
    private $isInStats = false;


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
     *
     * @return $this
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
     *
     * @return $this
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

    /**
     * @param int $currentTime
     *
     * @return $this
     */
    public function setCurrentTime($currentTime)
    {
        $this->currentTime = $currentTime;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentTime()
    {
        return $this->currentTime;
    }

    /**
     * @param int $ip
     *
     * @return $this
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * @return int
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param int $trackNumber
     *
     * @return $this
     */
    public function setTrackNumber($trackNumber)
    {
        $this->trackNumber = $trackNumber;

        return $this;
    }

    /**
     * @return int
     */
    public function getTrackNumber()
    {
        return $this->trackNumber;
    }

    /**
     * @param City $city
     *
     * @return $this
     */
    public function setCity(City $city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return City
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param Country $country
     *
     * @return $this
     */
    public function setCountry(Country $country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param int $timestamp
     *
     * @return $this
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param boolean $isPaid
     *
     * @return $this
     */
    public function setIsPaid($isPaid)
    {
        $this->isPaid = $isPaid;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsPaid()
    {
        return $this->isPaid;
    }

    /**
     * @param string $viewerId
     *
     * @return $this
     */
    public function setViewerId($viewerId)
    {
        $this->viewerId = $viewerId;

        return $this;
    }

    /**
     * @return string
     */
    public function getViewerId()
    {
        return $this->viewerId;
    }

    /**
     * @return int
     */
    public function getFingerprint()
    {
        return $this->fingerprint;
    }

    /**
     * @param int $fingerprint
     *
     * @return $this
     */
    public function setFingerprint($fingerprint)
    {
        $this->fingerprint = $fingerprint;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsInStats()
    {
        return $this->isInStats;
    }

    /**
     * @param boolean $isInStats
     *
     * @return $this
     */
    public function setIsInStats($isInStats)
    {
        $this->isInStats = $isInStats;

        return $this;
    }

}