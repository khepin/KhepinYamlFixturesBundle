<?php

namespace Khepin\Utils;

use Doctrine\ORM\EntityManager;
use \Mockery as m;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\DefaultQuoteStrategy;

class BaseTestCaseOrm extends \PHPUnit_Framework_TestCase
{
    protected $doctrine;

    private function getMockAnnotatedConfig()
    {
        $config = $this->getMock('Doctrine\ORM\Configuration');
        $config
                ->expects($this->once())
                ->method('getProxyDir')
                ->will($this->returnValue(__DIR__ . '/temp'))
        ;

        $config
                ->expects($this->once())
                ->method('getProxyNamespace')
                ->will($this->returnValue('Proxy'))
        ;

        $config
                ->expects($this->once())
                ->method('getAutoGenerateProxyClasses')
                ->will($this->returnValue(true))
        ;

        $config
                ->expects($this->once())
                ->method('getClassMetadataFactoryName')
                ->will($this->returnValue('Doctrine\\ORM\\Mapping\\ClassMetadataFactory'))
        ;

        $mappingDriver = $this->getMetadataDriverImplementation();

        $config
                ->expects($this->any())
                ->method('getMetadataDriverImpl')
                ->will($this->returnValue($mappingDriver))
        ;

        $config
                ->expects($this->any())
                ->method('getDefaultRepositoryClassName')
                ->will($this->returnValue('Doctrine\\ORM\\EntityRepository'))
        ;

        $quoteStrategy = new DefaultQuoteStrategy();

        $config
            ->expects($this->any())
            ->method('getQuoteStrategy')
            ->will($this->returnValue($quoteStrategy))
        ;

        return $config;
    }

    /**
     * Creates default mapping driver
     *
     * @return \Doctrine\ORM\Mapping\Driver\Driver
     */
    protected function getMetadataDriverImplementation()
    {
        return new AnnotationDriver(
                $_ENV['annotation_reader'],
                array(__DIR__.'/../Fixture/Entity')
        );
    }

    /**
     * EntityManager mock object together with
     * annotation mapping driver and pdo_sqlite
     * database in memory
     *
     * @param  EventManager  $evm
     * @return EntityManager
     */
    protected function getDoctrine()
    {
        $conn = array(
            'driver' => 'pdo_sqlite',
            'memory' => true,
            // 'path' => __DIR__.'/../db.sqlite',
        );

        $config = $this->getMockAnnotatedConfig();
        $em = EntityManager::create($conn, $config);

        $entities = array(
            'Khepin\\Fixture\\Entity\\Car',
            'Khepin\\Fixture\\Entity\\Driver',
            'Khepin\\Fixture\\Entity\\Owner'
        );

        $schema = array_map(function($class) use ($em) {
                    return $em->getClassMetadata($class);
                }, $entities);

        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema(array());
        $schemaTool->createSchema($schema);

        return $this->doctrine = m::mock(array(
            'getEntityManager'  => $em,
            'getManager'        => $em,
            )
        );
    }

}
