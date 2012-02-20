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

}