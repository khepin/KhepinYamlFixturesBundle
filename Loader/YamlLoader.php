<?php

namespace Khepin\YamlFixturesBundle\Loader;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Khepin\YamlFixturesBundle\Fixture\YamlAclFixture;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

class YamlLoader
{
    protected $bundles;

    /**
     * @var type
     */
    protected $kernel;

    /**
     * Doctrine entity manager.
     *
     * @var type
     */
    protected $object_manager;

    protected $acl_manager = null;

    /**
     * Array of all yml files containing fixtures that should be loaded.
     *
     * @var type
     */
    protected $fixture_files = [];

    /**
     * Maintains references to already created objects.
     *
     * @var type
     */
    protected $references = [];

    /**
     * The directory containing the fixtures files.
     *
     * @var string
     */
    protected $directory;

    public function __construct(KernelInterface $kernel, $bundles, $directory)
    {
        $this->bundles = $bundles;
        $this->kernel = $kernel;
        $this->directory = $directory;
    }

    /**
     * @param type $manager
     */
    public function setAclManager($manager = null)
    {
        $this->acl_manager = $manager;
    }

    /**
     * Returns a previously saved reference.
     *
     * @param type $reference_name
     *
     * @return type
     */
    public function getReference($reference_name)
    {
        return !is_null($reference_name) ? $this->references[$reference_name] : null;
    }

    /**
     * Sets a reference to an object.
     *
     * @param type $name
     * @param type $object
     */
    public function setReference($name, $object)
    {
        $this->references[$name] = $object;
    }

    /**
     * Gets all fixtures files.
     */
    protected function loadFixtureFiles()
    {
        foreach ($this->bundles as $bundle) {
            $file = '*';
            if (strpos($bundle, '/')) {
                list($bundle, $file) = explode('/', $bundle);
            }
            $path = $this->kernel->locateResource('@'.$bundle);
            $files = glob($path.$this->directory.'/'.$file.'.yml');
            $this->fixture_files = array_unique(array_merge($this->fixture_files, $files));
        }
    }

    /**
     * Loads the fixtures file by file and saves them to the database.
     */
    public function loadFixtures()
    {
        $this->loadFixtureFiles();
        foreach ($this->fixture_files as $file) {
            $fixture_data = Yaml::parse(file_get_contents($file));
            // if nothing is specified, we use doctrine orm for persistence
            $persistence = isset($fixture_data['persistence']) ? $fixture_data['persistence'] : 'orm';

            $persister = $this->getPersister($persistence);
            $manager = $persister->getManagerForClass($fixture_data['model']);

            $fixture = $this->getFixtureClass($persistence);
            $fixture = new $fixture($fixture_data, $this, $file);
            $fixture->load($manager, func_get_args());
        }

        if (!is_null($this->acl_manager)) {
            foreach ($this->fixture_files as $file) {
                $fixture = new YamlAclFixture($file, $this);
                $fixture->load($this->acl_manager, func_get_args());
            }
        }
    }

    /**
     * Remove all fixtures from the database.
     */
    public function purgeDatabase($persistence, $databaseName = null, $withTruncate = false)
    {
        $purgetools = [
            'orm'       => [
                'purger'    => 'Doctrine\Common\DataFixtures\Purger\ORMPurger',
                'executor'  => 'Doctrine\Common\DataFixtures\Executor\ORMExecutor',
            ],
            'mongodb'   => [
                'purger'    => 'Doctrine\Common\DataFixtures\Purger\MongoDBPurger',
                'executor'  => 'Doctrine\Common\DataFixtures\Executor\MongoDBExecutor',
            ],
        ];
        // Retrieve the correct purger and executor
        $purge_class = $purgetools[$persistence]['purger'];
        $executor_class = $purgetools[$persistence]['executor'];

        // Instanciate purger and executor
        $persister = $this->getPersister($persistence);
        $entityManagers = ($databaseName)
            ? [$persister->getManager($databaseName)]
            : $persister->getManagers();

        foreach ($entityManagers as $entityManager) {
            $purger = new $purge_class($entityManager);
            if ($withTruncate && $purger instanceof ORMPurger) {
                $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
            }
            $executor = new $executor_class($entityManager, $purger);
            // purge
            $executor->purge();
        }
    }

    /*
     * Returns the doctrine persister for the given persistence layer
     * @return ManagerRegistry
     */

    public function getPersister($persistence)
    {
        $managers = [
            'orm'       => 'doctrine',
            'mongodb'   => 'doctrine_mongodb',
        ];

        return $this->kernel->getContainer()->get($managers[$persistence]);
    }

    /**
     * @return string classname
     */
    public function getFixtureClass($persistence)
    {
        $classes = [
            'orm'       => 'Khepin\YamlFixturesBundle\Fixture\OrmYamlFixture',
            'mongodb'   => 'Khepin\YamlFixturesBundle\Fixture\MongoYamlFixture',
        ];

        return $classes[$persistence];
    }

    /**
     * @return the service with given id
     */
    public function getService($service_id)
    {
        return $this->kernel->getContainer()->get($service_id);
    }
}
