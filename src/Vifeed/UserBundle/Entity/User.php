<?php

namespace Vifeed\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
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
 *     groups={"FastRegistration"}
 * )
 * @UniqueEntity(
 *     fields={"usernameCanonical"},
 *     errorPath="username",
 *     message="fos_user.username.already_used",
 *     groups={"FastRegistration"}
 * )
 */
class User extends BaseUser
{
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
     *
     * @Assert\Choice(
     *      choices = {"advertiser", "publisher"},
     *      groups = {"FastRegistration"},
     *      message = "Выберите тип"
     * )
     * @Assert\NotBlank(groups={"FastRegistration"})
     */
    protected $type;

    /**
     * @var string
     *
     * @Assert\NotBlank(
     *      message = "fos_user.email.blank",
     *      groups = {"FastRegistration"}
     * )
     * @Assert\Length(
     *      min = 2,
     *      minMessage = "fos_user.email.short",
     *      max = 254,
     *      maxMessage = "fos_user.email.long",
     *      groups = {"FastRegistration"}
     * )
     * @Assert\Email(
     *      message = "fos_user.email.invalid",
     *      groups = {"FastRegistration"}
     * )
     */
    protected $email;


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
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
