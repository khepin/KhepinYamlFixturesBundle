<?php

namespace Khepin\Fixture\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document
 */
class Driver
{
    /**
     * @ODM\Id
     */
    private $id;

    /**
     * @ODM\ReferenceMany(targetDocument="Car")
     */
    private $cars = [];

    /**
     * @ODM\ReferenceOne(targetDocument="Car")
     */
    private $preferred_car;

    /**
     * @ODM\String
     *
     * @var type
     */
    private $name;

    public function getId()
    {
        return $this->id;
    }

    public function addCars(Car $car)
    {
        $this->cars[] = $car;
    }

    public function getCars()
    {
        return $this->cars;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setPreferredCar($car)
    {
        $this->preferred_car = $car;
    }

    public function getPreferredCar()
    {
        return $this->preferred_car;
    }
}
