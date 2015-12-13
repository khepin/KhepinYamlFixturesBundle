<?php

namespace Khepin\Fixture\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Owner
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
     * @ORM\OneToMany(targetEntity="Car", mappedBy="owner")
     *
     * @var ArrayCollection
     */
    private $owned_cars;

    public function __construct($name = null, Car $car = null)
    {
        $this->owned_cars = new ArrayCollection();
        $this->name = $name;
        if ($car) {
            $this->owned_cars->add($car);
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @param ArrayCollection $ownedCars
     */
    public function setOwnedCars($ownedCars)
    {
        $this->owned_cars = new ArrayCollection();
        foreach ($ownedCars as $car) {
            $this->owned_cars->add($car);
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getOwnedCars()
    {
        return $this->owned_cars;
    }
}
