<?php

namespace Vifeed\UserBundle\Entity;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Exclude;

/**
 * Company
 *
 * @ORM\Table(name="company")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Company
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Exclude
     */
    private $id;

    /**
     * пользователь
     *
     * @var User
     *
     * @ORM\OneToOne(targetEntity="User", inversedBy="company")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     * @Exclude
     **/
    private $user;

    /**
     * система налогообложения (enum): 'ОСН', 'УСН'
     *
     * @var string
     *
     * @ORM\Column(name="system", type="string", nullable=false,
     *  columnDefinition="ENUM('ОСН', 'УСН') NOT NULL")
     *
     * @Assert\NotBlank()
     * @Assert\Choice(choices={"ОСН", "УСН"})
     */
    private $system;

    /**
     * название кампании
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100)
     *
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * контактное лицо
     *
     * @var string
     *
     * @ORM\Column(name="contact_name", type="string", length=50)
     *
     * @Assert\NotBlank()
     */
    private $contactName;

    /**
     * должность контактного лица
     *
     * @var string
     *
     * @ORM\Column(name="position", type="string", length=50)
     *
     * @Assert\NotBlank()
     */
    private $position;

    /**
     * юридический адрес
     *
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=500)
     *
     * @Assert\NotBlank()
     */
    private $address;

    /**
     * телефон
     *
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=20)
     *
     * @Assert\NotBlank()
     */
    private $phone;

    /**
     * ИНН
     *
     * @var string
     *
     * @ORM\Column(name="inn", type="string", length=12)
     *
     * @Assert\NotBlank()
     */
    private $inn;

    /**
     * КПП
     *
     * @var string
     *
     * @ORM\Column(name="kpp", type="string", length=9)
     *
     * @Assert\NotBlank()
     */
    private $kpp;

    /**
     * БИК
     *
     * @var string
     *
     * @ORM\Column(name="bic", type="string", length=9)
     *
     * @Assert\NotBlank()
     */
    private $bic;

    /**
     * номер счёта
     *
     * @var string
     *
     * @ORM\Column(name="bank_account", type="string", length=20)
     *
     * @Assert\NotBlank()
     */
    private $bankAccount;

    /**
     * корр. счёт
     *
     * @var string
     *
     * @ORM\Column(name="correspondent_account", type="string", length=20)
     *
     * @Assert\NotBlank()
     */
    private $correspondentAccount;

    /**
     * одобрена менеджером
     *
     * @var string
     *
     * @ORM\Column(name="is_approved", type="boolean")
     */
    private $isApproved = false;

    /**
     *
     */
    public static function getSystemChoices()
    {
        return ['ОСН', 'УСН'];
    }

    /**
     * @ORM\PreUpdate
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(PreUpdateEventArgs $event)
    {
        if (!$this->isApproved()) {
            return;
        }

        $changes = $event->getEntityChangeSet();
        if ($changes) {
            $changes = array_keys($changes);
            foreach ($changes as $field) {
                if (!in_array($field, ['isApproved', 'contactName', 'position', 'phone'])) {
                    $this->setIsApproved(false);
                    break;
                }
            }

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
     * Set user
     *
     * @param User $user
     * @return Company
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
     * Set system
     *
     * @param string $system
     * @return Company
     */
    public function setSystem($system)
    {
        $this->system = $system;

        return $this;
    }

    /**
     * Get system
     *
     * @return string 
     */
    public function getSystem()
    {
        return $this->system;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Company
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
     * Set contactName
     *
     * @param string $contactName
     * @return Company
     */
    public function setContactName($contactName)
    {
        $this->contactName = $contactName;

        return $this;
    }

    /**
     * Get contactName
     *
     * @return string 
     */
    public function getContactName()
    {
        return $this->contactName;
    }

    /**
     * Set role
     *
     * @param string $role
     * @return Company
     */
    public function setPosition($role)
    {
        $this->position = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string 
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return Company
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string 
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $bankAccount
     * @return $this
     */
    public function setBankAccount($bankAccount)
    {
        $this->bankAccount = $bankAccount;

        return $this;
    }

    /**
     * @return string
     */
    public function getBankAccount()
    {
        return $this->bankAccount;
    }

    /**
     * @param string $bic
     * @return $this
     */
    public function setBic($bic)
    {
        $this->bic = $bic;

        return $this;
    }

    /**
     * @return string
     */
    public function getBic()
    {
        return $this->bic;
    }

    /**
     * @param string $correspondentAccount
     * @return $this
     */
    public function setCorrespondentAccount($correspondentAccount)
    {
        $this->correspondentAccount = $correspondentAccount;

        return $this;
    }

    /**
     * @return string
     */
    public function getCorrespondentAccount()
    {
        return $this->correspondentAccount;
    }

    /**
     * @param string $inn
     * @return $this
     */
    public function setInn($inn)
    {
        $this->inn = $inn;

        return $this;
    }

    /**
     * @return string
     */
    public function getInn()
    {
        return $this->inn;
    }

    /**
     * @param string $isApproved
     * @return $this
     */
    public function setIsApproved($isApproved)
    {
        $this->isApproved = $isApproved;

        return $this;
    }

    /**
     * @return string
     */
    public function isApproved()
    {
        return $this->isApproved;
    }

    /**
     * @param string $kpp
     * @return $this
     */
    public function setKpp($kpp)
    {
        $this->kpp = $kpp;

        return $this;
    }

    /**
     * @return string
     */
    public function getKpp()
    {
        return $this->kpp;
    }


}
