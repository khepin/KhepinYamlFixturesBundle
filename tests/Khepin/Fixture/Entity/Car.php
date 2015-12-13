<?php

namespace Khepin\Fixture\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Car
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="date", nullable=true)
     *
     * @var type
     */
    private $date_purchased;

    /**
     * @ORM\ManyToOne(targetEntity="Owner")
     *
     * @var Owner
     */
    private $owner;

    /**
     * @param string    $name
     * @param \DateTime $date_purchased
     */
    public function __construct($name = null, \DateTime $date_purchased = null)
    {
        $this->name = $name;
        $this->date_purchased = $date_purchased;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getDatePurchased()
    {
        return $this->date_purchased;
    }

    public function setDatePurchased(\DateTime $date_purchased)
    {
        $this->date_purchased = $date_purchased;
    }

    /**
     * @param Owner $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return Owner
     */
    public function getOwner()
    {
        return $this->owner;
    }
}
