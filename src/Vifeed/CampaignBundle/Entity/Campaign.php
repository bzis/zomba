<?php

namespace Vifeed\CampaignBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use DoctrineExtensions\Taggable\Taggable;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContext;
use Vifeed\SystemBundle\Entity\AgeRange;
use Vifeed\TagBundle\Entity\Tag;
use Vifeed\UserBundle\Entity\User;
use Vifeed\GeoBundle\Entity\Country;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Campaign
 *
 * @ORM\Table(
 *            name="campaign",
 *            indexes={
 *                      @ORM\Index(name="status", columns={"status"})
 *            }
 *           )
 * @ORM\Entity(repositoryClass="Vifeed\CampaignBundle\Repository\CampaignRepository")
 * @ORM\EntityListeners("Vifeed\CampaignBundle\EventListener\CampaignChangeListener")
 * @UniqueEntity(
 *              fields = {"user", "name"}, groups={"default"}, errorPath="name",
 *              message="У вас уже есть кампания с таким названием"
 * )
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Campaign implements Taggable
{
    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';

    /** работает */
    const STATUS_ON = 'on';
    /** на паузе */
    const STATUS_PAUSED = 'paused';
    /** завершена (это когда бюджет достигнут) */
    const STATUS_ENDED = 'ended';
    /** ожидает пополнения баланса */
    const STATUS_AWAITING = 'awaiting';
    /** архивирована */
    const STATUS_ARCHIVED = 'archived';


    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"default", "own"})
     */
    private $id;

    /**
     * хешированный id
     *
     * @var string
     *
     * @ORM\Column(name="hash_id", type="string", length=10, nullable=true, unique=true)
     *
     * @Serializer\Groups({"default", "own"})
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
     * навзание
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     *
     * @Assert\NotBlank(
     *      groups={"default", "running"},
     *      message="Название не должно быть пустым"
     * )
     *
     * @Serializer\Groups({"default", "own"})
     */
    private $name;

    /**
     * хеш youtube
     *
     * @var string
     *
     * @ORM\Column(name="hash", type="string", length=11)
     *
     * @Assert\NotBlank(
     *      groups={"default"},
     *      message="Хеш видео не должен быть пустым"
     * )
     * @Assert\Length(
     *      min=11,
     *      max=11,
     *      groups={"default"},
     *      exactMessage="Неверный хеш youtube"
     * )
     *
     * @Serializer\Groups({"default", "own"})
     */
    private $hash;

    /**
     * описание
     *
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=1024, nullable=true)
     *
     * @Serializer\Groups({"default", "own"})
     */
    private $description = '';

    /**
     * пол (male / female)
     *
     * @var string
     *
     * @ORM\Column(name="gender", type="string", columnDefinition="ENUM('male', 'female')")
     *
     * @Assert\Choice(
     *      choices = {"male", "female"},
     *      groups={"default"},
     *      message = "Выберите пол"
     * )
     *
     * @Serializer\Groups({"default", "own"})
     */
    private $gender;

    /**
     * текущий баланс кампании
     *
     * @var float
     *
     * @ORM\Column(name="balance", type="decimal", precision = 8, scale = 2)
     *
     * @Serializer\Groups({"own"})
     */
    private $balance = 0;

    /**
     * общий бюджет кампании
     *
     * @var float
     *
     * @ORM\Column(name="budget", type="decimal", precision = 8, scale = 2)
     *
     * @Assert\NotBlank(
     *      groups={"default"}
     * )
     * @Assert\GreaterThanOrEqual(
     *      value = 100,
     *      groups={"default"},
     *      message="Бюджет должен быть не меньше {{ compared_value }} руб"
     * )
     *
     * @Serializer\Groups({"own"})
     */
    private $generalBudget;

    /**
     * дневной бюджет кампании
     *
     * @var float
     *
     * @ORM\Column(name="daily_budget", type="decimal", precision = 8, scale = 2)
     *
     * @Assert\NotBlank(
     *      groups={"default"}
     * )
     *
     * @Assert\GreaterThanOrEqual(
     *      value = 0,
     *      groups={"default"},
     *      message="Бюджет не может быть отрицательным"
     * )
     *
     * @Serializer\Groups({"own"})
     */
    private $dailyBudget;

    /**
     * сумма использованных средств от общего бюджета
     *
     * @var float
     *
     * @ORM\Column(name="budget_used", type="decimal", precision = 8, scale = 2)
     *
     * @Serializer\Groups({"own"})
     */
    private $generalBudgetUsed = 0;

    /**
     * сумма использованных средств от дневного бюджета
     *
     * @var float
     *
     * @ORM\Column(name="daily_budget_used", type="decimal", precision = 8, scale = 2)
     *
     * @Serializer\Groups({"own"})
     */
    private $dailyBudgetUsed = 0;


    /**
     * дата последнего запуска
     *
     * @var \DateTime
     *
     * @ORM\Column(name="start_at", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"own"})
     */
    private $startAt;

    /**
     * дата окончания кампании (не используется)
     *
     * @var \DateTime
     *
     * @ORM\Column(name="end_at", type="datetime", nullable=true)
     *
     * @//Assert\DateTime(
     *      groups={"default"},
     *      message="Неверная дата"
     * )
     */
    private $endAt;

    /**
     * всего просмотров
     *
     * @var integer
     *
     * @ORM\Column(name="total_views", type="integer", nullable=true)
     *
     * @Serializer\Groups({"own"})
     */
    private $totalViews = 0;

    /**
     * всего оплаченных просмотров
     *
     * @var integer
     *
     * @ORM\Column(name="paid_views", type="integer", nullable=true)
     *
     * @Serializer\Groups({"own"})
     */
    private $paidViews = 0;

    /**
     * ставка
     *
     * @var float
     *
     * @ORM\Column(name="bid", type="decimal", precision = 5, scale = 2)
     *
     * @Assert\NotBlank(
     *      groups={"default"}
     * )
     * @Assert\Type(
     *      type="double",
     *      groups={"default"},
     *      message="Должно быть числом"
     * )
     * @Assert\GreaterThanOrEqual(
     *      value = 1,
     *      groups={"default"},
     *      message="Минимальная ставка - {{ compared_value }} руб"
     * )
     *
     * @Serializer\Groups({"own"})
     */
    private $bid;

    /**
     * статус
     *
     * @var string
     *
     * @ORM\Column(name="status", type="string", nullable=false,
     *  columnDefinition="ENUM('on', 'paused', 'ended', 'awaiting', 'archived') NOT NULL")
     *
     * @Assert\Choice(
     *      choices = {"on", "paused", "archived"},
     *      groups={"status"},
     *      message = "Выберите статус"
     * )
     *
     * @Serializer\Groups({"own"})
     */
    private $status = self::STATUS_PAUSED;

    /**
     * сериализованные данные из социальных сервисов
     *
     * @var array
     *
     * @ORM\Column(name="social_data", type="array", nullable=true, length=65532)
     *
     * @Serializer\Groups({"own"})
     */
    private $socialData;

    /**
     * сериализованные данные из youtube
     *
     * @var array
     *
     * @ORM\Column(name="youtube_data", type="array", nullable=true, length=65532)
     *
     * @Serializer\Groups({"own"})
     */
    private $youtubeData;

    /**
     * дата удаления кампании (NULL, если не удалена)
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * дата создания
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     * @Serializer\Groups({"own"})
     */
    private $createdAt;

    /**
     * дата последнего обновления
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="updated_at", type="datetime")
     * @Serializer\Groups({"own"})
     */
    private $updatedAt;

    /**
     * новая кампания (флаг, для уведомления партнеров)
     *
     * @var bool
     *
     * @ORM\Column(name="is_new", type="boolean", nullable=false)
     */
    private $isNew = true;

    /**
     * страны
     *
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Vifeed\GeoBundle\Entity\Country")
     * @ORM\JoinTable(name="campaign_country")
     *
     * @Serializer\Groups({"default", "own"})
     */
    private $countries;

    /**
     * теги
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @Serializer\Groups({"default", "own"})
     * @Serializer\Accessor(getter="getTagsArray")
     * @//Type("array<string>")
     */
    private $tags;

    /**
     * возрастные группы
     *
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Vifeed\SystemBundle\Entity\AgeRange")
     * @ORM\JoinTable(name="campaign_age_range")
     *
     * @Serializer\Groups({"default", "own"})
     */
    private $ageRanges;

    private $oldStatus;

    public function __construct()
    {
        $this->countries = new ArrayCollection();
        $this->tags = new ArrayCollection();

        $this->setSocialData('fbLikes', 0)
              ->setSocialData('fbComments', 0)
              ->setSocialData('fbShares', 0)
              ->setSocialData('vkLikes', 0)
              ->setSocialData('vkShares', 0)
              ->setSocialData('gplusShares', 0)
              ->setSocialData('linkedPlatforms', 0)
              ->setSocialData('updatedAt', null);
    }

    public static function getGenders()
    {
        return array(
              self::GENDER_MALE   => 'мужской',
              self::GENDER_FEMALE => 'женский'
        );
    }

    public static function getPublicStatuses()
    {
        return array(
              self::STATUS_ON       => 'работает',
              self::STATUS_PAUSED   => 'на паузе',
              self::STATUS_ARCHIVED => 'в архиве',
        );
    }

    public static function getStatuses()
    {
        return array_merge(self::getPublicStatuses(), [
              self::STATUS_AWAITING => 'ожидание',
              self::STATUS_ENDED    => 'закончена',
        ]);
    }

    /**
     * Returns the unique taggable resource type
     *
     * @return string
     */
    public function getTaggableType()
    {
        return 'campaign';
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

    /**
     * Остаток неизрасходованного общего бюджета
     *
     * @deprecated фактически, можно использовать баланс
     *
     * @return float
     */
    public function getGeneralBudgetRemains()
    {
        return round($this->getGeneralBudget() - $this->getGeneralBudgetUsed(), 2);
    }

    /**
     * Остаток неизрасходованного дневного бюджета
     *
     * @return float
     */
    public function getDailyBudgetRemains()
    {
        return round($this->getDailyBudget() - $this->getDailyBudgetUsed(), 2);
    }

    /**
     * @param int $amount amount
     *
     * @deprecated с 21.09.2014 использовать только для тестов. Для реальных кейсов использовать функции из CampaignRepository
     *
     * @return $this
     */
    public function updateDailyBudgetUsed($amount)
    {
        $this->dailyBudgetUsed = round($this->dailyBudgetUsed + $amount, 2);
        $this->generalBudgetUsed = round($this->generalBudgetUsed + $amount, 2);

        return $this;
    }

    /**
     * @return $this
     */
    public function resetDailyBudgetUsed()
    {
        $this->dailyBudgetUsed = 0;

        return $this;
    }

    /**
     * Ограничен ли дневной бюджет
     *
     * @return bool
     */
    public function hasDailyBudgetLimit()
    {
        return $this->getDailyBudget() > 0;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function setSocialData($key, $value)
    {
        $this->socialData[$key] = $value;

        return $this;
    }

    /**
     * @param null $key
     *
     * @return mixed
     */
    public function getSocialData($key = null)
    {
        if ($key !== null) {
            if (isset($this->socialData[$key])) {
                return $this->socialData[$key];
            } else {
                return null;
            }
        }

        return $this->socialData;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function setYoutubeData($key, $value)
    {
        $this->youtubeData[$key] = $value;

        return $this;
    }

    /**
     * @param null $key
     *
     * @return mixed
     */
    public function getYoutubeData($key = null)
    {
        if ($key !== null) {
            if (isset($this->youtubeData[$key])) {
                return $this->youtubeData[$key];
            } else {
                return null;
            }
        }

        return $this->youtubeData;
    }

    /**
     * callback-валидатор для dailyBudget
     *
     * @param ExecutionContext $context
     *
     * @Assert\Callback(
     *      groups={"default"}
     * )
     */
    public function isDailyLimitValid(ExecutionContext $context)
    {
        if ($this->dailyBudget != 0 && ($this->dailyBudget * 10 < $this->generalBudget)) {
            $context->addViolationAt('dailyBudget', 'Дневной бюджет не должен быть меньше 10% от бюджета кампании');
        }
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
     *
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
     * Set budget
     *
     * @param float $budget
     *
     * @return Campaign
     */
    public function setGeneralBudget($budget)
    {
        $this->generalBudget = $budget;

        return $this;
    }

    /**
     * Get budget
     *
     * @return float
     */
    public function getGeneralBudget()
    {
        return $this->generalBudget;
    }

    /**
     * @param float $dailyBudget
     *
     * @return $this
     */
    public function setDailyBudget($dailyBudget)
    {
        $this->dailyBudget = $dailyBudget;

        return $this;
    }

    /**
     * @return float
     */
    public function getDailyBudget()
    {
        return $this->dailyBudget;
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
     * @return Campaign
     */
    public function incrementTotalViews()
    {
        $this->totalViews++;

        return $this;
    }

    /**
     * increment paidViews
     *
     * @return Campaign
     */
    public function incrementPaidViews()
    {
        $this->paidViews++;

        return $this;
    }

    /**
     * @return int
     */
    public function getPaidViews()
    {
        return $this->paidViews;
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
     * Add countries
     *
     * @param Country $countries
     *
     * @return Campaign
     */
    public function addCountry(Country $countries)
    {
        $this->countries[] = $countries;

        return $this;
    }

    /**
     * Remove countries
     *
     * @param Country $countries
     */
    public function removeCountry(Country $countries)
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
     *
     * @return Campaign
     */
    public function removeAgeRange(AgeRange $ageRanges)
    {
        $this->ageRanges->removeElement($ageRanges);

        return $this;
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
     * @return Campaign
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     *
     * @return Campaign
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        if (!$this->oldStatus) {
            $this->oldStatus = $this->status;
        }
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getOldStatus()
    {
        return $this->oldStatus;
    }


    /**
     * израсходованный общий бюджет
     *
     * @return float
     */
    public function getGeneralBudgetUsed()
    {
        return $this->generalBudgetUsed;
    }

    /**
     * израсходованный дневной бюджет
     *
     * @return float
     */
    public function getDailyBudgetUsed()
    {
        return $this->dailyBudgetUsed;
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
     * @param boolean $isNew
     *
     * @return $this
     */
    public function setIsNew($isNew)
    {
        $this->isNew = $isNew;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isNew()
    {
        return $this->isNew;
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

    /**
     * @param float $balance
     *
     * @return $this
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * @return float
     */
    public function getBalance()
    {
        return $this->balance;
    }

}