<?php

namespace Khepin\YamlFixturesBundle\Fixture;

use Doctrine\Common\Persistence\ObjectManager;

abstract class AbstractFixture
{
    protected $tags = array();

    protected $file;

    protected $loader;

    protected $manager;

    public function __construct(array $data, $loader)
    {
        $this->file = $data;
        if (isset($this->file['tags'])) {
            $this->tags = $this->file['tags'];
        }
        $this->loader = $loader;
    }

    /**
     * Returns if the given tag is set for the current fixture
     * @param  type    $tag
     * @return boolean
     */
    public function hasTag(Array $tags)
    {
        // if no tags were specified, the fixture should always be loaded
        if (count($this->tags) == 0 || count(array_intersect($this->tags, $tags)) > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param the object on which to run the service calls
     */
    public function runServiceCalls($object)
    {
        if (isset($this->file['service_calls'])) {
            foreach ($this->file['service_calls'] as $call) {
                $s = $this->loader->getService($call['service']);
                $m = $call['method'];
                $s->$m($object);
            }
        }
    }

    public function load(ObjectManager $manager, $tags = null)
    {
        if (!$this->hasTag($tags)) {
            return;
        }
        $this->manager = $manager;
        $class = $this->file['model'];
        // Get the fields that are not "associations"
        $metadata = $this->getMetaDataForClass($class);

        foreach ($this->file['fixtures'] as $reference => $fixture_data) {
            $object = $this->createObject($class, $fixture_data, $metadata);
            $this->loader->setReference($reference, $object);
            if (!$this->isReverseSaveOrder()) {
                $manager->persist($object);
            }
        }

        if ($this->isReverseSaveOrder()) {
            $refs = array_keys($this->file['fixtures']);
            for ($i = (count($refs) - 1); $i>=0; $i--) {
                $manager->persist($this->loader->getReference($refs[$i]));
            }
        }

        $manager->flush();
    }

    public function getMetadataForClass($class)
    {
        $cmf = $this->manager->getMetadataFactory();

        return $cmf->getMetaDataFor($class);
    }

    /**
     * For fixtures that have relations to the same table, they need to appear
     * in the opposite order that they need to be saved.
     * @return boolean
     */
    public function isReverseSaveOrder()
    {
        if (!isset($this->file['save_in_reverse']) || $this->file['save_in_reverse'] == false) {
            return false;
        }

        return true;
    }

    /**
     * Extract the constructor arguments
     *
     * @param array $arguments
     * @return mixed
     */
    public function constructorArgs($arguments)
    {
        $constructArguments = array();

        if (is_array($arguments)) {
            foreach ($arguments as $argument) {
                if (is_array($argument)) {
                    if ($argument['type'] == 'datetime') {
                        $constructArguments[] = new \DateTime($argument['value']);
                    } elseif ($argument['type'] == 'reference') {
                        $constructArguments[] = $this->loader->getReference($argument['value']);
                    } else {
                        $constructArguments[] = $argument['value'];
                    }
                } else {
                    $constructArguments[] = $argument;
                }
            }
        } else {
            $constructArguments[] = $arguments;
        }

        return $constructArguments;
    }

    /**
     * Creates an instance with any given constructor args
     *
     * @param string $class
     * @param array $data
     * @return void
     */
    public function makeInstance($class, $data)
    {
        $class = new \ReflectionClass($class);
        $constructArguments = [];
        if (isset($data['__construct'])) {
            $constructArguments = $this->constructorArgs($data['__construct']);
        }

        return $class->newInstanceArgs($constructArguments);
    }

    /**
     * Creates and returns one object based on the given data and metadata
     *
     * @param $class object's class name
     * @param $data array of the object's fixture data
     * @param $metadata the class metadata for doctrine
     * @param $options options specific to each implementation
     * @return Object
     */
    abstract public function createObject($class, $data, $metadata, $options = array());
}
