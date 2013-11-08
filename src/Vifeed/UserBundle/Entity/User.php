<?php

namespace Vifeed\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * User
 *
 * @ORM\Table(name="vifeed_user")
 * @ORM\Entity(repositoryClass="Vifeed\UserBundle\Entity\UserRepository")
 * @UniqueEntity(
 *     fields={"emailCanonical"},
 *     errorPath="email",
 *     message="fos_user.email.already_used",
 *     groups={"AdvertiserRegistration", "PublisherRegistration"}
 * )
 * @UniqueEntity(
 *     fields={"usernameCanonical"},
 *     errorPath="username",
 *     message="fos_user.username.already_used",
 *     groups={"AdvertiserRegistration", "PublisherRegistration"}
 * )
 */
class User extends BaseUser
{
    const TYPE_ADVERTISER = 'advertiser';
    const TYPE_PUBLISHER = 'publisher';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", columnDefinition="ENUM('advertiser', 'publisher')")
     */
    protected $type;

    /**
     * @var string
     *
     * @Assert\NotBlank(
     *      message = "fos_user.email.blank",
     *      groups = {"AdvertiserRegistration", "PublisherRegistration"}
     * )
     * @Assert\Length(
     *      min = 2,
     *      minMessage = "fos_user.email.short",
     *      max = 254,
     *      maxMessage = "fos_user.email.long",
     *      groups = {"AdvertiserRegistration", "PublisherRegistration"}
     * )
     * @Assert\Email(
     *      message = "fos_user.email.invalid",
     *      groups = {"AdvertiserRegistration", "PublisherRegistration"}
     * )
     */
    protected $email;

    /**
     * @var string
     *
     * @Assert\NotBlank(
     *      message = "fos_user.password.blank",
     *      groups = {"PublisherRegistration"}
     * )
     * @Assert\Length(
     *      min = 6,
     *      minMessage = "fos_user.password.short",
     *      max = 4096,
     *      groups = {"PublisherRegistration"}
     * )
     */
    protected $plainPassword;

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
     * @ORM\Column(type="decimal", precision = 2)
     */
    protected $balance;


    /**
     * @return User
     */
    public function prepareForRegistration()
    {
        $this->setUsername($this->getEmail());

        if ($this->getType() == self::TYPE_ADVERTISER) {
            $generator = new SecureRandom();
            $pass = $generator->nextBytes(6);

            $this->setPlainPassword($pass)
                  ->setEnabled(true);

        } elseif ($this->getType() == self::TYPE_PUBLISHER) {

        }

        return $this;
    }

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

    /**
     * @param string $provider
     * @param mixed  $id
     *
     * @return User
     */
    public function setSocialID($provider, $id)
    {
        $var        = $this->getSocialIdName($provider);
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
}
