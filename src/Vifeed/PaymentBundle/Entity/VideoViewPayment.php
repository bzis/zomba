<?php

namespace Vifeed\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Vifeed\VideoViewBundle\Entity\VideoView;

/**
 * VideoViewPayment
 *
 * @ORM\Table(name="video_view_payment")
 * @ORM\Entity(repositoryClass="Vifeed\PaymentBundle\Repository\VideoViewPaymentRepository")
 */
class VideoViewPayment
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="Vifeed\VideoViewBundle\Entity\VideoView", fetch="EAGER")
     * @ORM\JoinColumn(name="video_view_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $videoView;

    /**
     * списано со счёта рекламодателя
     *
     * @var float
     *
     * @ORM\Column(type="decimal", precision = 5, scale = 2)
     */
    private $charged;

    /**
     * комиссия в пользу системы
     *
     * @var float
     *
     * @ORM\Column(type="decimal", precision = 5, scale = 2)
     */
    private $comission;

    /**
     * выплачено паблишеру
     *
     * @var float
     *
     * @ORM\Column(type="decimal", precision = 5, scale = 2)
     */
    private $paid;


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
     * @param float $charged
     */
    public function setCharged($charged)
    {
        $this->charged = $charged;

        return $this;
    }

    /**
     * @return float
     */
    public function getCharged()
    {
        return $this->charged;
    }

    /**
     * @param float $comission
     */
    public function setComission($comission)
    {
        $this->comission = $comission;

        return $this;
    }

    /**
     * @return float
     */
    public function getComission()
    {
        return $this->comission;
    }

    /**
     * @param float $paid
     */
    public function setPaid($paid)
    {
        $this->paid = $paid;

        return $this;
    }

    /**
     * @return float
     */
    public function getPaid()
    {
        return $this->paid;
    }

    /**
     * @param VideoView $videoView
     */
    public function setVideoView(VideoView $videoView)
    {
        $this->videoView = $videoView;

        return $this;
    }

    /**
     * @return VideoView
     */
    public function getVideoView()
    {
        return $this->videoView;
    }


}
