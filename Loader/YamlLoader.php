<?php

namespace Khepin\YamlFixturesBundle\Loader;

use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Util\Inflector;

class YamlLoader {

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

    public function __construct(\AppKernel $kernel, $doctrine, $bundles) {
        $this->object_manager = $doctrine->getEntityManager();
        $this->bundles = $bundles;
        $this->kernel = $kernel;
    }

    /**
     * Gets all fixtures files
     */
    protected function loadFixtureFiles($context = null) {
        foreach ($this->bundles as $bundle) {
            $path = $this->kernel->locateResource('@' . $bundle);
            $files = glob($path . 'DataFixtures/*.yml');
            $this->fixture_files = array_merge($this->fixture_files, $files);
            if(!is_null($context)){
                $files = glob($path.'DataFixtures/'. $context .'/*.yml');
                $this->fixture_files = array_merge($this->fixture_files, $files);
            }
        }
    }

    /**
     * Loads the fixtures file by file and saves them to the database 
     */
    public function loadFixtures($context = null) {
        $this->loadFixtureFiles($context);
        $cmf = $this->object_manager->getMetadataFactory();
        foreach ($this->fixture_files as $file) {
            $file = Yaml::parse($file);
            // The model class for all fixtures defined in this file
            $class = $file['model'];
            // Get the fields that are not "associations"
            $metadata = $cmf->getMetaDataFor($class);
            $mapping = array_keys($metadata->fieldMappings);
            
            foreach ($file['fixtures'] as $reference => $fixture) {
                // Instantiate new object
                $object = new $class;
                foreach ($fixture as $field => $value) {
                    // Add the fields defined in the fistures file
                    $method = Inflector::camelize('set_' . $field);
                    if(in_array($field, $mapping)){ // This is a standard field, not an association
                        // Dates need to be converted to DateTime objects
                        $type = $metadata->fieldMappings[$field]['type'];
                        if($type == 'datetime' OR $type == 'date'){
                            $value = new \DateTime($value);
                        }
                        $object->$method($value);
                    } else { // This field is an association, we load it from the references
                        $object->$method($this->references[$value]);
                    }
                }
                // Save a reference to the current object
                $this->references[$reference] = $object;
                $this->object_manager->persist($object);
            }
        }
        // Flush the complete object graph to the database
        $this->object_manager->flush();
    }

}