<?php

namespace Vifeed\PlatformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use DoctrineExtensions\Taggable\Taggable;
use Symfony\Component\Validator\Constraints as Assert;
use Vifeed\TagBundle\Entity\Tag;
use Vifeed\UserBundle\Entity\User;
use Vifeed\GeoBundle\Entity\Country;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Vifeed\SystemBundle\Validator\Constraints\UrlWithoutProtocol;
use JMS\Serializer\Annotation\Accessor;

/**
 * Platform
 *
 * @ORM\Table(name="platform", indexes={@ORM\Index(name="url_idx", columns={"url"})})
 * @ORM\Entity(repositoryClass="Vifeed\PlatformBundle\Repository\PlatformRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *      "platform" = "Platform",
 *      "vk" = "VkPlatform"
 * })
 * @UniqueEntity(
 *      fields={"url"}, groups={"new"},
 *      message="Площадка с таким адресом уже зарегистрирована в системе"
 * )
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Platform implements Taggable
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Groups({"own"})
     */
    private $id;

    /**
     * хешированный id
     *
     * @var string
     *
     * @ORM\Column(name="hash_id", type="string", length=10, nullable=true, unique=true)
     *
     * @Groups({"own"})
     */
    private $hashId;

    /**
     * пользователь
     *
     * @ORM\ManyToOne(targetEntity="Vifeed\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     **/
    private $user;

    /**
     * название площадки
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     *
     * @Assert\NotBlank(
     *      groups={"new", "existent"},
     *      message="Название не должно быть пустым"
     * )
     *
     * @Groups({"own"})
     */
    private $name;

    /**
     * адрес площадки
     *
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     *
     * @Assert\NotBlank(
     *      groups={"new"},
     *      message="Адрес не должен быть пустым"
     * )
     * @UrlWithoutProtocol(
     *      groups={"new"},
     *      message="Адрес должен быть валидным"
     * )
     *
     * @Groups({"own"})
     */
    private $url;

    /**
     * дата удаления кампании (NULL, если не удалена)
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * описание
     *
     * @var string
     * todo: хватит 255 символов?
     * @ORM\Column(name="description", type="string", length=255)
     *
     * @Assert\NotBlank(
     *      groups={"new", "existent"},
     *      message="Описание не должно быть пустым"
     * )
     * @Assert\Length(
     *      max=255,
     *      groups={"new", "existent"}
     * )
     *
     * @Groups({"own"})
     */
    private $description;

    /**
     * дата создания
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     * @Groups({"own"})
     */
    private $createdAt;

    /**
     * дата обновления пользователем
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="updated_at", type="datetime")
     * @Groups({"own"})
     */
    private $updatedAt;

    /**
     * страны
     *
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Vifeed\GeoBundle\Entity\Country")
     * @ORM\JoinTable(name="platform_country")
     *
     * @Groups({"own"})
     */
    private $countries;

    /**
     * теги
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @Groups({"own"})
     * @Accessor(getter="getTagsArray")
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
     * Returns the unique taggable resource type
     *
     * @return string
     */
    public function getTaggableType()
    {
        return 'platform';
    }

    /**
     * Returns the unique taggable resource identifier
     *
     * @return string
     */
    public function getTaggableId()
    {
        return $this->getId();
    }

    /**
     * @return array
     */
    public function getTagsArray()
    {
        $tags = [];
        foreach ($this->getTags() as $tag) {
            /** @var Tag $tag */
            $tags[] = $tag->getName();
        }

        return $tags;
    }

    public static function getAvailableTypes()
    {
        return ['site', 'vk'];
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
     * @return $this
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
     * Set url
     *
     * @param string $url
     *
     * @return $this
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
     *
     * @return $this
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
     * @param Country $country
     *
     * @return $this
     */
    public function addCountry(Country $country)
    {
        $this->countries[] = $country;

        return $this;
    }

    /**
     * Remove country
     *
     * @param Country $country
     */
    public function removeCountry(Country $country)
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
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTags()
    {
        $this->tags = $this->tags ?: new ArrayCollection();

        return $this->tags;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param \DateTime $deletedAt
     *
     * @return $this
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @param string $hashId
     *
     * @return $this
     */
    public function setHashId($hashId)
    {
        $this->hashId = $hashId;

        return $this;
    }

    /**
     * @return string
     */
    public function getHashId()
    {
        return $this->hashId;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return $this
     */
    public function updateUpdatedAt()
    {
        $this->updatedAt = new \DateTime();

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
