<?php

namespace Vifeed\PlatformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class VkVideo
 *
 * @ORM\Entity
 *
 * @package Vifeed\PlatformBundle\Entity
 */
class VkVideo extends PlatformVideo
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