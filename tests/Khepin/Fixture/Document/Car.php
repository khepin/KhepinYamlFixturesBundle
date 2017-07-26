<?php

namespace Khepin\Fixture\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document
 */
class Car
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
     * @ODM\Date
     * @var type
     */
    private $date_purchased;

    /**
     * @param string $name
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
}
