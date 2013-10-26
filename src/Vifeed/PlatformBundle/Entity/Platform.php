<?php

namespace Vifeed\PlatformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Platform
 *
 * @ORM\Table(name="platform")
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *      "platform" = "Platform",
 *      "vk" = "VkPlatform"
 * })
 */
class Platform
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
     *
     * @Assert\NotBlank(
     *      groups={"default"},
     *      message="Название не должно быть пустым"
     * )
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     *
     * @Assert\NotBlank(
     *      groups={"default"},
     *      message="Адрес не должен быть пустым"
     * )
     */
    private $url;

    /**
     * @var string
     * todo: хватит 255 символов?
     * @ORM\Column(name="description", type="string", length=255)
     *
     * @Assert\NotBlank(
     *      groups={"default"},
     *      message="Описание не должно быть пустым"
     * )
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="PlatformType")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     **/
    private $type;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Vifeed\CampaignBundle\Entity\Country")
     * @ORM\JoinTable(name="platform_country")
     */
    private $countries;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Vifeed\CampaignBundle\Entity\Tag")
     * @ORM\JoinTable(name="platform_tag")
     */
    private $tags;


    public function __construct()
    {
        $this->countries = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
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
     * @return Platform
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
     * Set type
     *
     * @param \Vifeed\PlatformBundle\Entity\PlatformType $type
     * @return Platform
     */
    public function setType(\Vifeed\PlatformBundle\Entity\PlatformType $type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return \Vifeed\PlatformBundle\Entity\PlatformType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Platform
     */
    public function setUrl($url)
    {
        $this->url = $url;
    
        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Platform
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
     * Add country
     *
     * @param \Vifeed\CampaignBundle\Entity\Country $country
     * @return Platform
     */
    public function addCountry(\Vifeed\CampaignBundle\Entity\Country $country)
    {
        $this->countries[] = $country;
    
        return $this;
    }

    /**
     * Remove country
     *
     * @param \Vifeed\CampaignBundle\Entity\Country $country
     */
    public function removeCountry(\Vifeed\CampaignBundle\Entity\Country $country)
    {
        $this->countries->removeElement($country);
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
     * @param \Vifeed\CampaignBundle\Entity\Tag $tag
     * @return Platform
     */
    public function addTag(\Vifeed\CampaignBundle\Entity\Tag $tag)
    {
        $this->tags[] = $tag;
    
        return $this;
    }

    /**
     * Remove tags
     *
     * @param \Vifeed\CampaignBundle\Entity\Tag $tag
     */
    public function removeTag(\Vifeed\CampaignBundle\Entity\Tag $tag)
    {
        $this->tags->removeElement($tag);
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
}