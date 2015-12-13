<?php

namespace Khepin\Tests;

use Khepin\Utils\BaseTestCaseOrm;
use Khepin\YamlFixturesBundle\Loader\YamlLoader;
use Mockery as m;

class YamlFixtureTest extends BaseTestCaseOrm
{
    protected $kernel = null;

    public function setUp()
    {
        $this->getDoctrine();
        $service = m::mock()->shouldReceive('lowerCaseName')->withAnyArgs()->andReturnUsing(
            function ($car) {
                    $car->setName(strtolower($car->getName()));
            }
        )->mock();
        $container = m::mock('Container')
                ->shouldReceive('get')->with('my_service')->andReturn($service)
                ->shouldReceive('get')->withAnyArgs()->andReturn($this->doctrine)
                ->mock();
        $this->kernel = m::mock(
            '\Symfony\Component\HttpKernel\KernelInterface',
            [
                    'locateResource' => __DIR__.'/simple_loading/',
                    'getContainer'   => $container,
            ]
        );
    }

    public function testSimpleLoading()
    {
        $loader = new YamlLoader($this->kernel, ['SomeBundle'], 'DataFixtures');
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

    public function testLoadSingleFile()
    {
        $loader = new YamlLoader($this->kernel, ['SomeBundle/cars'], 'DataFixtures');
        $loader->loadFixtures();

        $em = $this->doctrine->getManager();
        $cars = $em->getRepository('Khepin\Fixture\Entity\Car')->findAll();
        $this->assertEquals(2, count($cars));
        $drivers = $em->getRepository('Khepin\Fixture\Entity\Driver')->findAll();
        $this->assertEquals(0, count($drivers));
    }

    public function testSingleFileLoadedOnlyOnce()
    {
        $loader = new YamlLoader($this->kernel, ['SomeBundle/cars', 'SomeBundle'], 'DataFixtures');
        $loader->loadFixtures();

        $em = $this->doctrine->getManager();
        $cars = $em->getRepository('Khepin\Fixture\Entity\Car')->findAll();
        $this->assertEquals(2, count($cars));
        $drivers = $em->getRepository('Khepin\Fixture\Entity\Driver')->findAll();
        $this->assertEquals(0, count($drivers));
    }

    public function testContext()
    {
        $loader = new YamlLoader($this->kernel, ['SomeBundle'], 'DataFixtures');
        $loader->loadFixtures('french_cars');

        $repo = $this->doctrine->getEntityManager()->getRepository('Khepin\Fixture\Entity\Car');
        $cars = $repo->findAll();
        $this->assertEquals(4, count($cars));

        $car = $repo->findOneBy(['name' => 'Peugeot']);
        $this->assertEquals('Peugeot', $car->getName());

        $car = $repo->findOneBy(['name' => 'BMW']);
        $this->assertEquals('BMW', $car->getName());
    }

    public function testWithAssociation()
    {
        $loader = new YamlLoader($this->kernel, ['SomeBundle'], 'DataFixtures');
        $loader->loadFixtures('with_drivers');

        $repo = $this->doctrine->getEntityManager()->getRepository('Khepin\Fixture\Entity\Driver');
        $driver = $repo->findOneBy(['name' => 'Mom']);
        $this->assertEquals($driver->getCar()->getName(), 'Mercedes');
        $driver = $repo->findOneBy(['name' => 'Dad']);
        $this->assertEquals($driver->getCar()->getName(), 'BMW');

        $this->assertEquals(get_class($driver->getCar()), 'Khepin\Fixture\Entity\Car');
    }

    public function testPurge()
    {
        $loader = new YamlLoader($this->kernel, ['SomeBundle'], 'DataFixtures');
        $loader->loadFixtures('with_drivers');
        $loader->purgeDatabase('orm');

        $cars = $this->doctrine->getEntityManager()->getRepository('Khepin\Fixture\Entity\Car')->findAll();
        $this->assertEmpty($cars);
        $drivers = $this->doctrine->getEntityManager()->getRepository('Khepin\Fixture\Entity\Driver')->findAll();
        $this->assertEmpty($drivers);
    }

    public function testPurgeWithTruncate()
    {
        $loader = new YamlLoader($this->kernel, ['SomeBundle'], 'DataFixtures');
        $loader->loadFixtures('with_drivers');

        $cars = $this->doctrine->getEntityManager()->getRepository('Khepin\Fixture\Entity\Car')->findAll();
        $firstCar = $cars[0];
        $firstCarIdBefore = $firstCar->getId();

        $loader->purgeDatabase('orm', null, true);

        $cars = $this->doctrine->getEntityManager()->getRepository('Khepin\Fixture\Entity\Car')->findAll();
        $this->assertEmpty($cars);

        $loader->loadFixtures('with_drivers');

        $cars = $this->doctrine->getEntityManager()->getRepository('Khepin\Fixture\Entity\Car')->findAll();
        $firstCar = $cars[0];
        $firstCarIdAfter = $firstCar->getId();

        $this->assertEquals($firstCarIdBefore, $firstCarIdAfter);
    }

    public function testNullValuesInAssociations()
    {
        $loader = new YamlLoader($this->kernel, ['SomeBundle'], 'DataFixtures');
        $loader->loadFixtures('with_drivers');

        $repo = $this->doctrine->getEntityManager()->getRepository('Khepin\Fixture\Entity\Driver');

        $driver = $repo->findOneBy(['name' => 'Mom']);
        $this->assertEquals(get_class($driver->getCar()), 'Khepin\Fixture\Entity\Car');
        $this->assertEquals($driver->getCar()->getName(), 'Mercedes');
        $this->assertEquals($driver->getSecondCar(), null);

        $driver = $repo->findOneBy(['name' => 'Son']);
        $this->assertEquals(get_class($driver->getCar()), 'Khepin\Fixture\Entity\Car');
        $this->assertEquals($driver->getCar()->getName(), 'BMW');
        $this->assertEquals($driver->getSecondCar(), null);

        $driver = $repo->findOneBy(['name' => 'Dad']);
        $this->assertEquals(get_class($driver->getCar()), 'Khepin\Fixture\Entity\Car');
        $this->assertEquals($driver->getCar()->getName(), 'BMW');
        $this->assertEquals(get_class($driver->getSecondCar()), 'Khepin\Fixture\Entity\Car');
        $this->assertEquals($driver->getSecondCar()->getName(), 'Mercedes');
    }

    public function testServiceCalls()
    {
        $loader = new YamlLoader($this->kernel, ['SomeBundle'], 'DataFixtures');
        $loader->loadFixtures('service');

        $repo = $this->doctrine->getManager()->getRepository('Khepin\Fixture\Entity\Car');
        $car = $repo->findOneByName('toyota');
        $this->assertTrue('toyota' === $car->getName());
    }

    public function testArrayAssociation()
    {
        $loader = new YamlLoader($this->kernel, ['SomeBundle'], 'DataFixtures');
        $loader->loadFixtures();

        $em = $this->doctrine->getEntityManager();
        $owner = $em->getRepository('Khepin\Fixture\Entity\Owner')->findOneById(1);
        $this->assertEquals(2, count($owner->getOwnedCars()));

        $car1 = $owner->getOwnedCars()->get(0);
        $this->assertEquals(get_class($car1), 'Khepin\Fixture\Entity\Car');
        $this->assertEquals('Mercedes', $car1->getName());

        $car2 = $owner->getOwnedCars()->get(1);
        $this->assertEquals(get_class($car2), 'Khepin\Fixture\Entity\Car');
        $this->assertEquals('BMW', $car2->getName());
    }

    public function testConstructor()
    {
        $loader = new YamlLoader($this->kernel, ['SomeBundle'], 'DataFixtures');
        $loader->loadFixtures('construct');

        $repo = $this->doctrine->getManager()->getRepository('Khepin\Fixture\Entity\Car');
        $car = $repo->findOneByName('Ford');
        $this->assertEquals('Ford', $car->getName());
        $this->assertInstanceOf('\DateTime', $car->getDatePurchased());

        $repo = $this->doctrine->getManager()->getRepository('Khepin\Fixture\Entity\Owner');
        $owner = $repo->findOneByName('Some Small Company');
        $this->assertEquals(1, count($owner->getOwnedCars()));
        $this->assertEquals('Some Small Company', $owner->getName());
    }
}
