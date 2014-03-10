<?php

namespace Khepin\YamlFixturesBundle\Loader;

use Khepin\YamlFixturesBundle\Fixture\YamlAclFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Component\Yaml\Yaml;

class YamlLoader
{
    private $bundles;

    /**
     *
     * @var type
     */
    private $kernel;

    /**
     * Doctrine entity manager
     * @var type
     */
    private $object_manager;

    private $acl_manager = null;

    /**
     * Array of all yml files containing fixtures that should be loaded
     * @var type
     */
    private $fixture_files = array();

    /**
     * Maintains references to already created objects
     * @var type
     */
    private $references = array();

    /**
     * The directory containing the fixtures files
     *
     * @var string
     */
    private $directory;

    public function __construct(\AppKernel $kernel, $bundles, $directory)
    {
        $this->bundles = $bundles;
        $this->kernel = $kernel;
        $this->directory = $directory;
    }

    /**
     *
     * @param type $manager
     */
    public function setAclManager($manager = null)
    {
        $this->acl_manager = $manager;
    }

    /**
     * Returns a previously saved reference
     * @param  type $reference_name
     * @return type
     */
    public function getReference($reference_name)
    {
        return !is_null($reference_name) ? $this->references[$reference_name] : null;
    }

    /**
     * Sets a reference to an object
     * @param type $name
     * @param type $object
     */
    public function setReference($name, $object)
    {
        $this->references[$name] = $object;
    }
    
    /**
     * Returns an array of tags from loadFixtures method arguments
     * @param array $load_fixtures_args
     */
    protected function extractTags(array $load_fixtures_args) {
      if (count($load_fixtures_args)) {
        return array(0 => $load_fixtures_args[0]);
      } else {
        return $load_fixtures_args;
      }
    }

    /**
     * If it was specified in the command line, returns the single bundle fixture file
     * @param array $load_fixtures_args
     */
    protected function extractSingleBundle(array $load_fixtures_args) {
      $single_bundle = (2 == count($load_fixtures_args) ? $load_fixtures_args[1] : null);
      if ($single_bundle && !in_array($single_bundle, $this->bundles)) {
        throw new \Exception('Bundle fixture ' . $single_bundle . " doesn't exist.");
      }

      return $single_bundle;
    }

    /**
     * Gets all fixtures files
     * @param string $single_bundle Single bundle fixture file if it was specified.
     */
    protected function loadFixtureFiles($single_bundle = null)
    {
        if ($single_bundle) {
          $this->bundles = array($single_bundle);
        }

        foreach ($this->bundles as $bundle) {
            $file = '*';
            if (strpos($bundle, '/')) {
                list($bundle, $file) = explode('/', $bundle);
            }
            $path = $this->kernel->locateResource('@' . $bundle);
            $files = glob($path . $this->directory . '/'.$file.'.yml');
            $this->fixture_files = array_unique(array_merge($this->fixture_files, $files));
        }
    }

    /**
     * Loads the fixtures file by file and saves them to the database
     */
    public function loadFixtures()
    {
        $tags = $this->extractTags(func_get_args());
        $single_bundle = $this->extractSingleBundle(func_get_args());

        $this->loadFixtureFiles($single_bundle);
        foreach ($this->fixture_files as $file) {
            $fixture_data = Yaml::parse($file);
            // if nothing is specified, we use doctrine orm for persistence
            $persistence = isset($fixture_data['persistence']) ? $fixture_data['persistence'] : 'orm';
            $fixture = $this->getFixtureClass($persistence);
            $fixture = new $fixture($fixture_data, $this, $file);
            $fixture->load($this->getManager($persistence), $tags);
        }

        if (!is_null($this->acl_manager)) {
            foreach ($this->fixture_files as $file) {
                $fixture = new YamlAclFixture($file, $this);
                $fixture->load($this->acl_manager, $tags);
            }
        }
    }

    /**
     * Remove all fixtures from the database
     */
    public function purgeDatabase($persistence, $withTruncate = false)
    {
        $purgetools = array(
            'orm'       => array(
                'purger'    => 'Doctrine\Common\DataFixtures\Purger\ORMPurger',
                'executor'  => 'Doctrine\Common\DataFixtures\Executor\ORMExecutor',
            ),
            'mongodb'   => array(
                'purger'    => 'Doctrine\Common\DataFixtures\Purger\MongoDBPurger',
                'executor'  => 'Doctrine\Common\DataFixtures\Executor\MongoDBExecutor',
            )
        );
        // Retrieve the correct purger and executor
        $purge_class = $purgetools[$persistence]['purger'];
        $executor_class = $purgetools[$persistence]['executor'];
        // Instanciate purger and executor
        $purger = new $purge_class($this->getManager($persistence));

        // Check if the purger supports setting the purge mode
        if ($withTruncate && $purger instanceof ORMPurger) {
            $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        }

        $executor = new $executor_class($this->getManager($persistence), $purger);
        // purge
        $executor->purge();
    }

    /**
     * Returns a doctrine object manager for the given persistence layer
     * @return ObjectManager
     */
    public function getManager($persistence)
    {
        $managers = array(
            'orm'       => 'doctrine',
            'mongodb'   => 'doctrine_mongodb',
        );

        return $this->kernel->getContainer()
            ->get($managers[$persistence])->getManager();
    }

    /**
     * @return string classname
     */
    public function getFixtureClass($persistence)
    {
        $classes = array(
            'orm'       => 'Khepin\YamlFixturesBundle\Fixture\OrmYamlFixture',
            'mongodb'   => 'Khepin\YamlFixturesBundle\Fixture\MongoYamlFixture'
        );

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
