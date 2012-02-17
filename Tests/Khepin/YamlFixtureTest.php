<?php

namespace Khepin\Tests;

use \Mockery as m;
use Khepin\YamlFixturesBundle\Loader\YamlLoader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;

class YamlFixtureTest extends \PHPUnit_Framework_TestCase {

    protected $em;

    /**
     * EntityManager mock object together with
     * annotation mapping driver and pdo_sqlite
     * database in memory
     *
     * @param EventManager $evm
     * @return EntityManager
     */
    protected function getMockSqliteEntityManager() {
        $conn = array(
            'driver' => 'pdo_sqlite',
            'memory' => true,
        );

        $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(array('./fixtures'));
        $em = EntityManager::create($conn, $config);
        
        $entities = array();

        $schema = array_map(function($class) use ($em) {
                    return $em->getClassMetadata($class);
                }, $entities);

        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema(array());
        $schemaTool->createSchema($schema);
        return $this->em = $em;
    }

    public function setUp() {
        $this->getMockSqliteEntityManager();
    }

    public function testSomething() {
        $this->assertEquals(1, 1);
        $doctrine = m::mock(array('getEntityManager' => $this->em));
        $kernel = m::mock('alias:AppKernel');
        $loader = new YamlLoader($kernel, $doctrine, array('SomeBundle'));
    }

}