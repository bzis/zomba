<?php

namespace Vifeed\VideoViewBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RefererBlackList
 *
 * @ORM\Entity()
 * @ORM\Table(name="referer_black_list")
 */
class RefererBlackList
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
     * @ORM\Column(name="domain", type="string", nullable=false)
     */
    private $domain;


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
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param mixed $domain
     *
     * @return $this
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }



}