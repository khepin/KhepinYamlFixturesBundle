<?php

namespace Khepin\Fixture\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document
 */
class Driver {

    /**
     * @ODM\Id
     */
    private $id;

    /**
     * @ODM\ReferenceMany(targetDocument="Car")
     */
    private $cars = array();

    /**
     * @ODM\String
     * @var type
     */
    private $name;

    public function getId() {
        return $this->id;
    }

    public function addCars(Car $car){
        $this->cars[] = $car;
    }

    public function getCars(){
        return $this->cars;
    }

    public function getName(){
        return $this->name;
    }

    public function setName($name){
        $this->name = $name;
    }
}