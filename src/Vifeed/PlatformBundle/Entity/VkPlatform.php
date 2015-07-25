<?php

namespace Vifeed\PlatformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class VkPlatform
 *
 * @ORM\Entity
 *
 * @package Vifeed\PlatformBundle\Entity
 */
class VkPlatform extends Platform
{
    /**
     * @var integer
     *
     * @ORM\Column(name="vk_id", type="integer")
     *
     * @Assert\NotBlank(
     *      groups={"vk"}
     * )
     * @Assert\Type(
     *      type="digit",
     *      groups={"vk"}
     * )
     */
    private $vkId;

    /**
     * @return int
     */
    public function getVkId()
    {
        return $this->vkId;
    }

    /**
     * @param $vkId
     *
     * @return $this
     */
    public function setVkId($vkId)
    {
        $this->vkId = $vkId;

        return $this;
    }
}