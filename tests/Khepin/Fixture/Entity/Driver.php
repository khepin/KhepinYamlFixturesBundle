<?php

namespace Khepin\Fixture\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity 
 */
class Driver {

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="Car")
     */
    private $car;
    
    /**
     * @ORM\Column(type="string")
     * @var type 
     */
    private $name;

    public function getId() {
        return $this->id;
    }
    
    public function setCar(Car $car){
        $this->car = $car;
    }
    
    public function getCar(){
        return $this->car;
    }
    
    public function getName(){
        return $this->name;
    }
    
    public function setName($name){
        $this->name = $name;
    }
}