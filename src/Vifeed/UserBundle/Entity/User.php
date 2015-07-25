<?php

namespace Vifeed\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation as Serializer;

/**
 * User
 *
 * @ORM\Table(name="vifeed_user")
 * @ORM\Entity(repositoryClass="Vifeed\UserBundle\Entity\UserRepository")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(
 *     fields={"emailCanonical"},
 *     errorPath="email",
 *     message="fos_user.email.already_used",
 *     groups = {"ApiRegistration", "ApiProfile"}
 * )
 * @UniqueEntity(
 *     fields={"usernameCanonical"},
 *     errorPath="username",
 *     message="fos_user.username.already_used",
 *     groups = {"ApiRegistration", "ApiProfile"}
 * )
 */
class User extends BaseUser
{
    const TYPE_ADVERTISER = 'advertiser';
    const TYPE_PUBLISHER = 'publisher';

    const NOTIFICATION_NONE = 0;
    const NOTIFICATION_NEW_CAMPAIGN_EMAIL = 1;
    const NOTIFICATION_NEW_CAMPAIGN_SMS = 2;
    const NOTIFICATION_ADVERTISER_NEWS = 4;

    /**
     * @var integer
     *
     * @Serializer\Groups({"user"})
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * тип пользователя (enum): 'advertiser', 'publisher'
     *
     * @var string
     *
     * @ORM\Column(name="type", type="string", columnDefinition="ENUM('advertiser', 'publisher')")
     *
     * @Serializer\Groups({"user"})
     *
     * @Assert\Choice(
     *      choices = {"advertiser", "publisher"},
     *      groups = {"ApiRegistration"}
     * )
     * @Assert\NotBlank(
     *      groups = {"ApiRegistration"}
     * )
     */
    protected $type;

    /**
     * email
     *
     * @var string
     *
     * @Serializer\Groups({"user"})
     *
     * @Assert\NotBlank(
     *      message = "fos_user.email.blank",
     *      groups = {"ApiRegistration", "ApiProfile"}
     * )
     * @Assert\Length(
     *      min = 2,
     *      minMessage = "fos_user.email.short",
     *      max = 254,
     *      maxMessage = "fos_user.email.long",
     *      groups = {"ApiRegistration", "ApiProfile"}
     * )
     * @Assert\Email(
     *      message = "fos_user.email.invalid",
     *      groups = {"ApiRegistration", "ApiProfile"}
     * )
     */
    protected $email;

    /**
     * пароль
     *
     * @var string
     *
     * @Assert\NotBlank(
     *      message = "fos_user.password.blank",
     *      groups = {"ApiRegistration", "ApiChangePassword"}
     * )
     * @Assert\Length(
     *      min = 6,
     *      minMessage = "fos_user.password.short",
     *      max = 4096,
     *      groups = {"ApiRegistration", "ApiChangePassword"}
     * )
     */
    protected $plainPassword;

    /**
     * дата последнего логина
     *
     * @var \DateTime
     *
     * @Serializer\Groups({"user"})
     */
    protected $lastLogin;

    /**
     * @var string
     * @ORM\Column(type="string", name="vk_id", nullable=true)
     */
    protected $vkID;

    /**
     * @var array
     * @ORM\Column(type="array", name="social_data", nullable=true, length=65532)
     */
    protected $socialData;

    /**
     * баланс
     *
     * @ORM\Column(type="decimal", precision = 11, scale = 2)
     * @Serializer\Groups({"user"})
     */
    protected $balance = 0;

    /**
     * подтверждён ли email
     * @var boolean
     * @ORM\Column(type="boolean", name="email_confirmed")
     */
    protected $emailConfirmed = false;

    /**
     * @ORM\ManyToMany(targetEntity="Group")
     * @ORM\JoinTable(name="vifeed_user_groups",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    protected $groups;

    /**
     * имя
     *
     * @var string
     * @ORM\Column(type="string", name="first_name", length=50, nullable=true)
     *
     * @Serializer\Groups({"user"})
     */
    protected $firstName;

    /**
     * фамилия
     *
     * @var string
     * @ORM\Column(type="string", name="surname", length=50, nullable=true)
     *
     * @Serializer\Groups({"user"})
     */
    protected $surname;

    /**
     * телефон
     *
     * @var int
     * @ORM\Column(type="string", name="phone", length=15, nullable=true)
     *
     * @Serializer\Groups({"user"})
     */
    protected $phone;

    /**
     * @var Company
     *
     * @ORM\OneToOne(targetEntity="Company", mappedBy="user", cascade={"persist", "remove"})
     */
    protected $company;

    /**
     * настройки уведомлений
     * 
     * @ORM\Column(type="smallint", name="notification", nullable=false)
     * @Serializer\Groups({"user"})
     * @Serializer\Accessor(getter="getNotification", setter="setNotification")
     * @Serializer\Type("array<string>")
     */
    protected $notification;


    /**
     * @param string $provider
     *
     * @static
     *
     * @return string
     */
    public static function getSocialIdName($provider)
    {
        switch ($provider) {
            case 'VK':
                return 'vkID';
        }
        throw new \Exception('Неизвестный провайдер ' . $provider);
    }

    public static function getTypes()
    {
        return [
              self::TYPE_ADVERTISER => 'рекламодатель',
              self::TYPE_PUBLISHER  => 'паблишер',
        ];
    }
    /**
     * @param string $provider
     * @param mixed  $id
     *
     * @return User
     */
    public function setSocialID($provider, $id)
    {
        $var = $this->getSocialIdName($provider);
        $this->$var = $id;

        return $this;
    }

    /**
     * @param string $provider
     *
     * @return bool
     */
    public function getSocialDataByProvider($provider)
    {
        $data = $this->getSocialData();
        if (isset($data[$provider])) {
            return $data[$provider];
        }

        return false;
    }

    /**
     * @param string $provider
     * @param mixed  $socialData
     */
    public function setSocialDataByProvider($provider, $socialData)
    {
        $data = $this->getSocialData();
        if (!is_array($data)) {
            $data = array();
        }
        $data[$provider] = $socialData;
        $this->setSocialData($data);
    }

    /**
     * @param string $provider
     */
    public function removeSocialDataByProvider($provider)
    {
        $data = $this->getSocialData();
        if (isset($data[$provider])) {
            unset($data[$provider]);
        }

        $this->setSocialData($data);
    }

    /**
     * @ORM\PrePersist
     */
    public function setDefaultNotification()
    {
        if ($this->getId()) {
            return;
        }

        if ($this->type == self::TYPE_ADVERTISER) {
            $this->notification = self::NOTIFICATION_ADVERTISER_NEWS;
        } else {
            $this->notification = self::NOTIFICATION_NEW_CAMPAIGN_EMAIL;
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
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return User
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getVkID()
    {
        return $this->vkID;
    }

    /**
     * @param string $vkID
     *
     * @return User
     */
    public function setVkID($vkID)
    {
        $this->vkID = $vkID;

        return $this;
    }

    /**
     * @return array
     */
    public function getSocialData()
    {
        return $this->socialData;
    }

    /**
     * @param array $socialData
     *
     * @return User
     */
    private function setSocialData($socialData)
    {
        $this->socialData = $socialData;

        return $this;

    }

    /**
     * @return mixed
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param mixed $balance
     *
     * @return User
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * @param boolean $emailConfirmed
     *
     * @return $this
     */
    public function setEmailConfirmed($emailConfirmed)
    {
        $this->emailConfirmed = $emailConfirmed;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isEmailConfirmed()
    {
        return $this->emailConfirmed;
    }

    /**
     * @param string $firstName
     *
     * @return $this
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param int $phone
     *
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return int
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $surname
     *
     * @return $this
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * @return \Vifeed\UserBundle\Entity\Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param Company $company
     *
     * @return $this
     */
    public function setCompany(Company $company)
    {
        $this->company = $company;
        $company->setUser($this);

        return $this;
    }

    /**
     * @param array $notification
     *
     * @return $this
     */
    public function setNotification($notification)
    {
        if (!is_array($notification)) {
            $notification = [$notification];
        }
        $this->notification = self::NOTIFICATION_NONE;
        if (isset($notification['email']) && $notification['email']) {
            $this->notification |= self::NOTIFICATION_NEW_CAMPAIGN_EMAIL;
        }
        if (isset($notification['sms']) && $notification['sms']) {
            $this->notification |= self::NOTIFICATION_NEW_CAMPAIGN_SMS;
        }
        if (isset($notification['news']) && $notification['news']) {
            $this->notification |= self::NOTIFICATION_ADVERTISER_NEWS;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getNotification()
    {
        $notification = [];

        if ($this->getType() == self::TYPE_ADVERTISER) {
            $notification['news'] = ($this->notification & self::NOTIFICATION_ADVERTISER_NEWS) ? 1 : 0;
        } elseif ($this->getType() == self::TYPE_PUBLISHER) {
            $notification['email'] = ($this->notification & self::NOTIFICATION_NEW_CAMPAIGN_EMAIL) ? 1 : 0;
            $notification['sms'] = ($this->notification & self::NOTIFICATION_NEW_CAMPAIGN_SMS) ? 1 : 0;
        }

        return $notification;
    }

}
