<?php

namespace Vifeed\GeoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * Country
 *
 * @ORM\Table(
 *            name="net_country",
 *            indexes={
 *                      @ORM\Index(name="code", columns={"code"}),
 *                      @ORM\Index(name="name_ru", columns={"name_ru"}),
 *                      @ORM\Index(name="name_en", columns={"name_en"})
 *            })
 * @ORM\Entity
 */
class Country
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Groups({"default", "own"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name_ru", type="string", length=100, nullable=true)
     *
     * @Groups({"default", "own"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="name_en", type="string", length=100, nullable=true)
     */
    private $nameEn;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=2, nullable=true)
     */
    private $code;


    public function __toString()
    {
        return $this->getName();
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
     * Set name
     *
     * @param string $name
     *
     * @return Country
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
     * Set code
     *
     * @param string $code
     *
     * @return Country
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}