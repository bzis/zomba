<?php

namespace Vifeed\PlatformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     * @return VkPlatform
     */
    public function setVkId($vkId)
    {
        $this->vkId = $vkId;

        return $this;
    }
}