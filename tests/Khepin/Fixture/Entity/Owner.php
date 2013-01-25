<?php

namespace Khepin\Fixture\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

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
     * @var ArrayCollection
     */
    private $owned_cars;

    public function __construct()
    {
        $this->owned_cars = new ArrayCollection();
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
