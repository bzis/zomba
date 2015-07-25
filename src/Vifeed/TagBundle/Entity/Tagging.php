<?php

namespace Vifeed\TagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DoctrineExtensions\Taggable\Entity\Tagging as BaseTagging;

/**
 * Tagging
 *
 * @ORM\Table(name="tagging")
 * @ORM\Entity
 */
class Tagging extends BaseTagging
{


    /**
     * @ORM\Id
     * @ORM\Column(name="resource_type", type="string", length=50)
     */
    protected $resourceType;

    /**
     * @ORM\Id
     * @ORM\Column(name="resource_id", type="string", length=50)
     */
    protected $resourceId;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Tag", inversedBy="tagging")
     * @ORM\JoinColumn(name="tag_id", referencedColumnName="id")
     **/
    protected $tag;
}