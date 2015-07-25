<?php

namespace Vifeed\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Payment\CoreBundle\Entity\PaymentInstruction;
use Vifeed\UserBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Order
 *
 * @ORM\Table(name="payment_order")
 * @ORM\Entity(repositoryClass="Vifeed\PaymentBundle\Repository\OrderRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Order
{
    const STATUS_NEW = 'new';
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\OneToOne(targetEntity="JMS\Payment\CoreBundle\Entity\PaymentInstruction")
     * @ORM\JoinColumn(name="payment_instruction_id", referencedColumnName="id")
     */
    private $paymentInstruction;

    /**
     * @ORM\Column(type="decimal", precision = 11, scale = 2)
     *
     * @Assert\NotBlank(
     *      groups={"default"}
     * )
     * @Assert\GreaterThan(
     *      value = 0,
     *      groups={"default"},
     *      message="Должно быть положительным числом"
     * )
     * @Assert\LessThan(
     *      value = 1000000000,
     *      groups={"default"},
     *      message="Не больше одного миллиарда за раз!"
     * )
     */
    private $amount;

    /**
     * @ORM\ManyToOne(targetEntity="Vifeed\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $user;

    /**
     * @var array
     * @ORM\Column(type="array", name="bill_data", nullable=true, length=255)
     */
    protected $billData;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", nullable=false,
     *  columnDefinition="ENUM('new', 'pending', 'paid', 'cancelled') NOT NULL")
     */
    private $status = self::STATUS_NEW;


    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatTimestamps()
    {
        $this->setUpdatedAt(new \DateTime());

        if ($this->getCreatedAt() == null) {
            $this->setCreatedAt(new \DateTime());
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Order
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Order
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
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     *
     * @return Order
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     *
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return PaymentInstruction
     */
    public function getPaymentInstruction()
    {
        return $this->paymentInstruction;
    }

    /**
     * @param mixed $paymentInstruction
     *
     * @return $this
     */
    public function setPaymentInstruction(PaymentInstruction $paymentInstruction)
    {
        $this->paymentInstruction = $paymentInstruction;

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
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @param array $billData
     *
     * @return $this
     */
    public function setBillData($billData)
    {
        $this->billData = $billData;

        return $this;
    }

    /**
     * @return array
     */
    public function getBillData()
    {
        return $this->billData;
    }

}
