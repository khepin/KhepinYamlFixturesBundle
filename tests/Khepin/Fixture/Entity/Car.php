<?php

namespace Khepin\Fixture\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity() 
 */
class Car {
    
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
     * @var type 
     */
    private $date_purchased;
    
    public function getId(){
        return $this->id;
    }
    
    public function getName(){
        return $this->name;
    }
    
    public function setName($name){
        $this->name = $name;
    }
    
    public function getDatePurchased(){
        return $this->date_purchased;
    }
    
    public function setDatePurchased(\DateTime $date_purchased){
        $this->date_purchased = $date_purchased;
    }
}