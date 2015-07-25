<?php

namespace Vifeed\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Vifeed\UserBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * Wallet
 *
 * @ORM\Table(name="wallet")
 * @ORM\Entity(repositoryClass="Vifeed\PaymentBundle\Repository\WalletRepository")
 */
class Wallet
{
    const TYPE_YANDEX_MONEY = 'yandex';
    const TYPE_WEB_MONEY = 'wm';
    const TYPE_QIWI = 'qiwi';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Groups({"default"})
     */
    private $id;

    /**
     * пользователь
     *
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Vifeed\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * тип (enum): yandex / wm / qiwi
     *
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=false,
     *  columnDefinition="ENUM('yandex', 'wm', 'qiwi') NOT NULL")
     *
     * @Assert\NotBlank(
     *      groups={"default"}
     * )
     * @Assert\Choice(
     *      choices = {"yandex", "wm", "qiwi"},
     *      groups={"default"},
     *      message = "Выберите тип"
     * )
     *
     * @Groups({"default"})
     */
    private $type;

    /**
     * номер
     *
     * @var string
     *
     * @ORM\Column(name="number", type="string", length=20)
     *
     * @Assert\NotBlank(
     *      groups={"default"}
     * )
     *
     * @Groups({"default"})
     */
    private $number;

    public function __toString()
    {
        return $this->getType() . ' ' . $this->getNumber();
    }

    public static function getTypes()
    {
        return [
              self::TYPE_QIWI         => 'Qiwi',
              self::TYPE_WEB_MONEY    => 'WebMoney',
              self::TYPE_YANDEX_MONEY => 'Яндекс.Деньги'
        ];
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
     * Set type
     *
     * @param string $type
     *
     * @return Wallet
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set number
     *
     * @param string $number
     *
     * @return Wallet
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return Wallet
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
