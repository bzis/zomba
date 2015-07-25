<?php

namespace Vifeed\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Vifeed\UserBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * Withdrawal
 *
 * @ORM\Table(name="withdrawal")
 * @ORM\Entity(repositoryClass="Vifeed\PaymentBundle\Repository\WithdrawalRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Withdrawal
{
    const STATUS_CREATED = 'new';
    const STATUS_ERROR = 'error';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_OK = 'ok';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Vifeed\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @var Wallet
     *
     * @ORM\ManyToOne(targetEntity="Vifeed\PaymentBundle\Entity\Wallet")
     * @ORM\JoinColumn(name="wallet_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @Assert\NotBlank(
     *      groups={"default"}
     * )
     */
    private $wallet;


    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="decimal", precision = 9, scale = 2)
     *
     * @Assert\NotBlank(
     *      groups={"default"}
     * )
     * @Assert\GreaterThanOrEqual(
     *      value = 1000,
     *      groups={"default"},
     *      message="Минимальная сумма вывода - 1000 рублей"
     * )
     */
    private $amount;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="string", nullable=false,
     *  columnDefinition="ENUM('new', 'ok', 'error', 'cancelled') NOT NULL")
     *
     * @Groups({"default"})
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;


    /**
     * PrePersist
     *
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTime();
        }
        $this->updatedAt = new \DateTime();
    }

    /**
     * PreUpdate
     *
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }

    public static function getStatuses()
    {
        return array(
              self::STATUS_CREATED   => 'создан',
              self::STATUS_ERROR     => 'ошибка',
              self::STATUS_CANCELLED => 'отклонен',
              self::STATUS_OK        => 'OK',
        );
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
     * Set user
     *
     * @param User $user
     *
     * @return Withdrawal
     */
    public function setUser(User $user)
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

    /**
     * Set amount
     *
     * @param float $amount
     *
     * @return Withdrawal
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Withdrawal
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Withdrawal
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Withdrawal
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \Vifeed\PaymentBundle\Entity\Wallet
     */
    public function getWallet()
    {
        return $this->wallet;
    }

    /**
     * @param \Vifeed\PaymentBundle\Entity\Wallet $wallet
     *
     * @return Withdrawal
     */
    public function setWallet(Wallet $wallet)
    {
        $this->wallet = $wallet;

        return $this;
    }
}
