<?php

namespace Vifeed\CampaignBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Campaign
 *
 * @ORM\Table(name="campaign")
 * @ORM\Entity
 */
class Campaign
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;

    /**
     * @var string
     * todo: подумать над более оптимальным типом. Enum?
     *
     * @ORM\Column(name="gender", type="string", length=1)
     */
    private $gender;

    /**
     * @var float
     *
     * @ORM\Column(name="max_bid", type="decimal")
     */
    private $maxBid;

    /**
     * @var float
     *
     * @ORM\Column(name="budget", type="decimal")
     */
    private $budget;

    /**
     * @var string
     * todo: подумать над более оптимальным типом. Enum?
     *
     * @ORM\Column(name="budget_type", type="string", length=1)
     */
    private $budgetType;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_at", type="datetime")
     */
    private $startAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_at", type="datetime")
     */
    private $endAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="total_views", type="integer")
     */
    private $totalViews;

    /**
     * @var float
     *
     * @ORM\Column(name="bid", type="decimal")
     */
    private $bid;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Platform")
     * @ORM\JoinTable(name="campaign_platform")
     */
    private $platforms;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Country")
     * @ORM\JoinTable(name="campaign_country")
     */
    private $countries;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Tag")
     * @ORM\JoinTable(name="campaign_tag")
     */
    private $tags;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AgeRange")
     * @ORM\JoinTable(name="campaign_age_range")
     */
    private $ageRanges;


    public function __construct()
    {
        $this->platforms = new ArrayCollection();
    }


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
     * Set name
     *
     * @param string $name
     *
     * @return Campaign
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Campaign
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set gender
     *
     * @param string $gender
     * @return Campaign
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set maxBid
     *
     * @param float $maxBid
     *
     * @return Campaign
     */
    public function setMaxBid($maxBid)
    {
        $this->maxBid = $maxBid;

        return $this;
    }

    /**
     * Get maxBid
     *
     * @return float
     */
    public function getMaxBid()
    {
        return $this->maxBid;
    }

    /**
     * Set budget
     *
     * @param float $budget
     *
     * @return Campaign
     */
    public function setBudget($budget)
    {
        $this->budget = $budget;

        return $this;
    }

    /**
     * Get budget
     *
     * @return float
     */
    public function getBudget()
    {
        return $this->budget;
    }

    /**
     * Set budgetType
     *
     * @param string $budgetType
     *
     * @return Campaign
     */
    public function setBudgetType($budgetType)
    {
        $this->budgetType = $budgetType;

        return $this;
    }

    /**
     * Get budgetType
     *
     * @return string
     */
    public function getBudgetType()
    {
        return $this->budgetType;
    }

    /**
     * Set startAt
     *
     * @param \DateTime $startAt
     *
     * @return Campaign
     */
    public function setStartAt($startAt)
    {
        $this->startAt = $startAt;

        return $this;
    }

    /**
     * Get startAt
     *
     * @return \DateTime
     */
    public function getStartAt()
    {
        return $this->startAt;
    }

    /**
     * Set endAt
     *
     * @param \DateTime $endAt
     *
     * @return Campaign
     */
    public function setEndAt($endAt)
    {
        $this->endAt = $endAt;

        return $this;
    }

    /**
     * Get endAt
     *
     * @return \DateTime
     */
    public function getEndAt()
    {
        return $this->endAt;
    }

    /**
     * Set totalViews
     *
     * @param integer $totalViews
     *
     * @return Campaign
     */
    public function setTotalViews($totalViews)
    {
        $this->totalViews = $totalViews;

        return $this;
    }

    /**
     * Get totalViews
     *
     * @return integer
     */
    public function getTotalViews()
    {
        return $this->totalViews;
    }

    /**
     * Set bid
     *
     * @param float $bid
     *
     * @return Campaign
     */
    public function setBid($bid)
    {
        $this->bid = $bid;

        return $this;
    }

    /**
     * Get bid
     *
     * @return float
     */
    public function getBid()
    {
        return $this->bid;
    }

    /**
     * get Platforms
     *
     * @return ArrayCollection
     */
    public function getPlatforms()
    {
        return $this->platforms;
    }

    /**
     * Add platforms
     *
     * @param Platform $platforms
     *
     * @return Campaign
     */
    public function addPlatform(Platform $platforms)
    {
        $this->platforms[] = $platforms;

        return $this;
    }

    /**
     * Remove platforms
     *
     * @param Platform $platforms
     */
    public function removePlatform(Platform $platforms)
    {
        $this->platforms->removeElement($platforms);
    }

    /**
     * Add countries
     *
     * @param Country $countries
     *
     * @return Campaign
     */
    public function addCountrie(Country $countries)
    {
        $this->countries[] = $countries;

        return $this;
    }

    /**
     * Remove countries
     *
     * @param Country $countries
     */
    public function removeCountrie(Country $countries)
    {
        $this->countries->removeElement($countries);
    }

    /**
     * Get countries
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCountries()
    {
        return $this->countries;
    }

    /**
     * Add tags
     *
     * @param Tag $tags
     *
     * @return Campaign
     */
    public function addTag(Tag $tags)
    {
        $this->tags[] = $tags;

        return $this;
    }

    /**
     * Remove tags
     *
     * @param Tag $tags
     */
    public function removeTag(Tag $tags)
    {
        $this->tags->removeElement($tags);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Add ageRanges
     *
     * @param AgeRange $ageRanges
     *
     * @return Campaign
     */
    public function addAgeRange(AgeRange $ageRanges)
    {
        $this->ageRanges[] = $ageRanges;

        return $this;
    }

    /**
     * Remove ageRanges
     *
     * @param AgeRange $ageRanges
     */
    public function removeAgeRange(AgeRange $ageRanges)
    {
        $this->ageRanges->removeElement($ageRanges);
    }

    /**
     * Get ageRanges
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAgeRanges()
    {
        return $this->ageRanges;
    }
}