<?php

namespace Khepin\Utils;

use Doctrine\ORM\EntityManager;
use \Mockery as m;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;

class BaseTestCaseMongo extends \PHPUnit_Framework_TestCase
{
    protected $doctrine;

    private function getMockAnnotatedConfig()
    {
        $mappingDriver = $this->getMetadataDriverImplementation();

        $config = m::mock('Doctrine\ODM\MongoDB\Configuration', array(
            'getProxyDir'                   => __DIR__.'/../temp',
            'getProxyNamespace'             => 'Proxy',
            'getAutoGenerateProxyClasses'   => true,
            'getClassMetadataFactoryName'   => 'Doctrine\\ODM\\MongoDB\\Mapping\\ClassMetadataFactory',
            'getMetadataDriverImpl'         => $mappingDriver,
            'getDefaultRepositoryClassName' => 'Doctrine\\ODM\\MongoDB\\DocumentRepository',
            'getMongoCmd'                   => '$',
            'getMetadataCacheImpl'          => null,
            'getHydratorDir'                => __DIR__.'/../temp',
            'getHydratorNamespace'          => 'Hydrator',
            'getAutoGenerateHydratorClasses'=> true,
            'getDefaultCommitOptions'       => array('safe' => true),
            'getDefaultDB'                  => 'khepinyamlfixturestest',
            'getRetryConnect'               => 2,
            'getRetryQuery'                 => 2,
            'getLoggerCallable'             => null,
        ));

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
                __DIR__.'/../Fixture/Document'
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
        $config = $this->getMockAnnotatedConfig();
        $dm = \Doctrine\ODM\MongoDB\DocumentManager::create(null, $config);

        return $this->doctrine = m::mock(array('getManager' => $dm));

        // $conn = array(
        //     'driver' => 'pdo_sqlite',
        //     'memory' => true,
        //     // 'path' => __DIR__.'/../db.sqlite',
        // );

        // $config = $this->getMockAnnotatedConfig();
        // $em = EntityManager::create($conn, $config);

        // $entities = array(
        //     'Khepin\\Fixture\\Entity\\Car',
        //     'Khepin\\Fixture\\Entity\\Driver'
        // );

        // $schema = array_map(function($class) use ($em) {
        //             return $em->getClassMetadata($class);
        //         }, $entities);

        // $schemaTool = new SchemaTool($em);
        // $schemaTool->dropSchema(array());
        // $schemaTool->createSchema($schema);
        // return $this->doctrine = m::mock(array(
        //     'getEntityManager'  => $em,
        //     'getManager'        => $em,
        //     )
        // );
    }

}
