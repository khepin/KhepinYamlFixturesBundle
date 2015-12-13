<?php

namespace Khepin\Fixture\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Driver
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Car")
     */
    private $car;

    /**
     * @ORM\ManyToOne(targetEntity="Car")
     * @ORM\JoinColumn(nullable=true)
     */
    private $secondCar;

    /**
     * @ORM\Column(type="string")
     *
     * @var type
     */
    private $name;

    public function getId()
    {
        return $this->id;
    }

    public function setCar(Car $car)
    {
        $this->car = $car;
    }

    public function getCar()
    {
        return $this->car;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setSecondCar($secondCar)
    {
        $this->secondCar = $secondCar;
    }

    public function getSecondCar()
    {
        return $this->secondCar;
    }
}
