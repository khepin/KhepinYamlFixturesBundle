<?php

namespace Khepin\Tests;

use \Mockery as m;
use Khepin\YamlFixturesBundle\Loader\YamlLoader;
use Khepin\Utils\BaseTestCaseOrm;

class YamlFixtureTest extends BaseTestCaseOrm {

    protected $kernel = null;

    public function setUp() {
        $this->getDoctrine();
    }

    public function testSimpleLoading() {
        $this->kernel = m::mock(
                        'AppKernel', array('locateResource' => __DIR__ . '/simple_loading/')
        );
        $loader = new YamlLoader($this->kernel, $this->doctrine, array('SomeBundle'));
        $loader->loadFixtures();

        $em = $this->doctrine->getEntityManager();
        $cars = $em->getRepository('Khepin\Fixture\Entity\Car')->findAll();
        $this->assertEquals(2, count($cars));
        $car = $cars[0];
        $this->assertEquals('Mercedes', $car->getName());
        $date = new \DateTime('2012-01-01');
        $this->assertEquals($date, $car->getDatePurchased());
        $this->assertEquals(get_class($date), get_class($car->getDatePurchased()));
        
        $car = $cars[1];
        $this->assertEquals('BMW', $car->getName());
    }

    public function testContext() {
        $this->kernel = m::mock(
                        'AppKernel', array('locateResource' => __DIR__ . '/simple_loading/')
        );
        $loader = new YamlLoader($this->kernel, $this->doctrine, array('SomeBundle'));
        $loader->loadFixtures('french_cars');

        $repo = $this->doctrine->getEntityManager()->getRepository('Khepin\Fixture\Entity\Car');
        $cars = $repo->findAll();
        $this->assertEquals(4, count($cars));

        $car = $repo->findOneBy(array('name' => 'Peugeot'));
        $this->assertEquals('Peugeot', $car->getName());

        $car = $repo->findOneBy(array('name' => 'BMW'));
        $this->assertEquals('BMW', $car->getName());
    }
    
    public function testWithAssociation(){
        $this->kernel = m::mock(
                        'AppKernel', array('locateResource' => __DIR__ . '/simple_loading/')
        );
        $loader = new YamlLoader($this->kernel, $this->doctrine, array('SomeBundle'));
        $loader->loadFixtures('with_drivers');
        
        $repo = $this->doctrine->getEntityManager()->getRepository('Khepin\Fixture\Entity\Driver');
        $driver = $repo->findOneBy(array('name' => 'Mom'));
        $this->assertEquals($driver->getCar()->getName(), 'Mercedes');
        $driver = $repo->findOneBy(array('name' => 'Dad'));
        $this->assertEquals($driver->getCar()->getName(), 'BMW');
        
        $this->assertEquals(get_class($driver->getCar()), 'Khepin\Fixture\Entity\Car');
    }
    
    public function testPurge(){
        $this->kernel = m::mock(
                        'AppKernel', array('locateResource' => __DIR__ . '/simple_loading/')
        );
        $loader = new YamlLoader($this->kernel, $this->doctrine, array('SomeBundle'));
        $loader->loadFixtures('with_drivers');
        $loader->purgeDatabase();
        
        $cars = $this->doctrine->getEntityManager()->getRepository('Khepin\Fixture\Entity\Car')->findAll();
        $this->assertEmpty($cars);
        $drivers = $this->doctrine->getEntityManager()->getRepository('Khepin\Fixture\Entity\Driver')->findAll();
        $this->assertEmpty($drivers);
    }

    public function testNullValuesInAssociations() {
        $this->kernel = m::mock(
            'AppKernel', array('locateResource' => __DIR__ . '/simple_loading/')
        );
        $loader = new YamlLoader($this->kernel, $this->doctrine, array('SomeBundle'));
        $loader->loadFixtures('with_drivers');

        $repo = $this->doctrine->getEntityManager()->getRepository('Khepin\Fixture\Entity\Driver');

        $driver = $repo->findOneBy(array('name' => 'Mom'));
        $this->assertEquals(get_class($driver->getCar()), 'Khepin\Fixture\Entity\Car');
        $this->assertEquals($driver->getCar()->getName(), 'Mercedes');
        $this->assertEquals($driver->getSecondCar(), null);

        $driver = $repo->findOneBy(array('name' => 'Son'));
        $this->assertEquals(get_class($driver->getCar()), 'Khepin\Fixture\Entity\Car');
        $this->assertEquals($driver->getCar()->getName(), 'BMW');
        $this->assertEquals($driver->getSecondCar(), null);

        $driver = $repo->findOneBy(array('name' => 'Dad'));
        $this->assertEquals(get_class($driver->getCar()), 'Khepin\Fixture\Entity\Car');
        $this->assertEquals($driver->getCar()->getName(), 'BMW');
        $this->assertEquals(get_class($driver->getSecondCar()), 'Khepin\Fixture\Entity\Car');
        $this->assertEquals($driver->getSecondCar()->getName(), 'Mercedes');
    }

}