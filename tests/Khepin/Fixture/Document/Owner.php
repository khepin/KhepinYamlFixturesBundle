<?php

namespace Khepin\Fixture\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ODM\Document
 */
class Owner
{

    /**
     * @ODM\Id
     */
    private $id;

    /**
     * @ODM\String
     */
    private $name;

    /**
     * @ODM\ReferenceMany(targetDocument="Car")
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
