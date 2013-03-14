<?php

namespace Khepin\Tests;

use \Mockery as m;
use Khepin\YamlFixturesBundle\Loader\YamlLoader;
use Khepin\Utils\BaseTestCaseMongo;

class MongoFixtureTest extends BaseTestCaseMongo
{
    protected $kernel = null;

    public function setUp()
    {
        $this->getDoctrine();
        $service = m::mock()->shouldReceive('lowerCaseName')->withAnyArgs()->andReturnUsing(
                function($car){
                        $car->setName(strtolower($car->getName()));
                }
        )->mock();
        $container = m::mock('Container')
                ->shouldReceive('get')->with('my_service')->andReturn($service)
                ->shouldReceive('get')->withAnyArgs()->andReturn($this->doctrine)
                ->mock();
        $this->kernel = m::mock(
                'AppKernel', array(
                        'locateResource' => __DIR__ . '/mongo/',
                        'getContainer'   => $container
                )
        );
        $loader = new YamlLoader($this->kernel, array('SomeBundle'), 'DataFixtures');
        $loader->purgeDatabase('mongodb');
    }

    public function testSimpleLoading()
    {
        $loader = new YamlLoader($this->kernel, array('SomeBundle'), 'DataFixtures');
        $loader->loadFixtures();

        $dm = $this->doctrine->getManager();
        $cars = $dm->getRepository('Khepin\Fixture\Document\Car')->findAll();
        $this->assertEquals(2, count($cars));
        $car = $dm->getRepository('Khepin\Fixture\Document\Car')->findOneBy(array('name' => 'Mercedes'));
        $this->assertEquals('Mercedes', $car->getName());
        $date = new \DateTime('2012-01-01');
        $this->assertEquals($date, $car->getDatePurchased());
        $this->assertEquals(get_class($date), get_class($car->getDatePurchased()));
    }

    public function testLoadSingleFile()
    {
        $loader = new YamlLoader($this->kernel, array('SomeBundle/cars'), 'DataFixtures');
        $loader->loadFixtures();

        $dm = $this->doctrine->getManager();
        $cars = $dm->getRepository('Khepin\Fixture\Document\Car')->findAll();
        $this->assertEquals(2, count($cars));
        $drivers = $dm->getRepository('Khepin\Fixture\Document\Driver')->findAll();
        $this->assertEquals(0, count($drivers));
    }

    public function testSingleFileLoadedOnlyOnce()
    {
        $loader = new YamlLoader($this->kernel, array('SomeBundle/cars', 'SomeBundle'), 'DataFixtures');
        $loader->loadFixtures();

        $em = $this->doctrine->getManager();
        $cars = $em->getRepository('Khepin\Fixture\Document\Car')->findAll();
        $this->assertEquals(2, count($cars));
        $drivers = $em->getRepository('Khepin\Fixture\Document\Driver')->findAll();
        $this->assertEquals(0, count($drivers));
    }

    public function testPurge()
    {
        $loader = new YamlLoader($this->kernel, array('SomeBundle'), 'DataFixtures');
        $loader->loadFixtures();
        $loader->purgeDatabase('mongodb');

        $cars = $this->doctrine->getManager()->getRepository('Khepin\Fixture\Document\Car')->findAll();
        $this->assertEquals($cars->count(), 0);
        $drivers = $this->doctrine->getManager()->getRepository('Khepin\Fixture\Document\Driver')->findAll();
        $this->assertEquals($drivers->count(), 0);
    }

    public function testContext()
    {
        $loader = new YamlLoader($this->kernel, array('SomeBundle'), 'DataFixtures');
        $loader->loadFixtures('french_cars');

        $repo = $this->doctrine->getManager()->getRepository('Khepin\Fixture\Document\Car');
        $cars = $repo->findAll();
        $this->assertEquals(5, count($cars));

        $car = $repo->findOneBy(array('name' => 'Peugeot'));
        $this->assertEquals('Peugeot', $car->getName());

        $car = $repo->findOneBy(array('name' => 'BMW'));
        $this->assertEquals('BMW', $car->getName());
    }

    public function testReferenceOne()
    {
        $loader = new YamlLoader($this->kernel, array('SomeBundle'), 'DataFixtures');
        $loader->loadFixtures('with_drivers', 'family_cars');

        $repo = $this->doctrine->getManager()->getRepository('Khepin\Fixture\Document\Driver');
        $driver = $repo->findOneBy(array('name' => 'Mom'));

        $this->assertEquals('Mercedes', $driver->getPreferredCar()->getName());
        $this->assertNotEquals('BMW', $driver->getPreferredCar()->getName());
    }

    public function testReferenceMany()
    {
        $loader = new YamlLoader($this->kernel, array('SomeBundle'), 'DataFixtures');
        $loader->loadFixtures('with_drivers', 'family_cars');

        $repo = $this->doctrine->getManager()->getRepository('Khepin\Fixture\Document\Driver');
        $driver = $repo->findOneBy(array('name' => 'Mom'));
        $this->assertEquals($driver->getCars()->count(), 3);
        $cars = $driver->getCars();
        $car = $cars[0];
        $this->assertEquals('Mercedes', $car->getName());
        $driver = $repo->findOneBy(array('name' => 'Dad'));
        $this->assertEquals($driver->getCars()->count(), 3);
    }

    public function testServiceCalls()
    {
        $loader = new YamlLoader($this->kernel, array('SomeBundle'), 'DataFixtures');
        $loader->loadFixtures('service');

        $repo = $this->doctrine->getManager()->getRepository('Khepin\Fixture\Document\Car');
        $car = $repo->findOneBy(array('name' => 'toyota'));
        $this->assertTrue('toyota' === $car->getName());
    }

    public function testEmbedOne()
    {
        $loader = new YamlLoader($this->kernel, array('SomeBundle'), 'DataFixtures');
        $loader->loadFixtures('embed_one');

        $repo = $this->doctrine->getManager()->getRepository('Khepin\Fixture\Document\Article');
        $articles = $repo->findAll();
        $this->assertEquals($articles->count(), 1);
        $article = $articles->getNext();
        $this->assertInstanceOf('Khepin\Fixture\Document\Article', $article);
        $author = $article->getAuthor();
        $this->assertInstanceOf('Khepin\Fixture\Document\Author', $author);

        $this->assertEquals($author->getName(), 'Paul');
    }

    public function testEmbedMany()
    {
        $loader = new YamlLoader($this->kernel, array('SomeBundle'), 'DataFixtures');
        $loader->loadFixtures('embed_many');

        $repo = $this->doctrine->getManager()->getRepository('Khepin\Fixture\Document\Article');
        $articles = $repo->findAll();
        $this->assertEquals($articles->count(), 1);
        $article = $articles->getNext();
        $this->assertInstanceOf('Khepin\Fixture\Document\Article', $article);
        $tags = $article->getTags();
        $this->assertEquals('YAML', $tags[0]->getName());
        $this->assertEquals($tags->count(), 2);
    }

}
