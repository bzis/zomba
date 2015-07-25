<?php

namespace Vifeed\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="vifeed_user_ip_log")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class UserIpLog
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
     * пользователь
     *
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", onDelete="CASCADE", nullable=false)
     **/
    private $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="logged_at", type="datetime")
     */
    private $loggedAt;

    /**
     * @ORM\Column(name="ip", type="bigint")
     */
    private $ip;

    /**
     *
     */
    public function __construct(User $user, $ip)
    {
        $this->user = $user;
        $this->ip = ip2long($ip);
        $this->loggedAt = new \DateTime();
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



}
