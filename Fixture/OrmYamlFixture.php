<?php

namespace Khepin\YamlFixturesBundle\Fixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\Inflector;

class OrmYamlFixture extends AbstractFixture {

    public function load(ObjectManager $manager, $tags = null) {
        if(!$this->hasTag($tags)){
            return;
        }
        $cmf = $manager->getMetadataFactory();
        // The model class for all fixtures defined in this file
        $class = $this->file['model'];
        // Get the fields that are not "associations"
        $metadata = $cmf->getMetaDataFor($class);
        $mapping = array_keys($metadata->fieldMappings);
        $associations = array_keys($metadata->associationMappings);

        foreach ($this->file['fixtures'] as $reference => $fixture) {
            // Instantiate new object
            $object = new $class;
            foreach ($fixture as $field => $value) {
                // Add the fields defined in the fistures file
                $method = Inflector::camelize('set_' . $field);
                // 
                if (in_array($field, $mapping)) {
                    // Dates need to be converted to DateTime objects
                    $type = $metadata->fieldMappings[$field]['type'];
                    if ($type == 'datetime' OR $type == 'date') {
                        $value = new \DateTime($value);
                    }
                    $object->$method($value);
                } else if (in_array($field, $associations)) { // This field is an association, we load it from the references
                    $object->$method($this->loader->getReference($value));
                } else {
                    // It's a method call that will set a field named differently
                    // eg: FOSUserBundle ->setPlainPassword sets the password after
                    // Encrypting it
                    $object->$method($value);
                }
            }
            // Save a reference to the current object
            $this->loader->setReference($reference, $object);
            if(!$this->isReverseSaveOrder()){
                $manager->persist($object);
            }
        }
        if($this->isReverseSaveOrder()){
            $refs = array_keys($this->file['fixtures']);
            for($i = (count($refs) - 1); $i>=0; $i--){
                $manager->persist($this->loader->getReference($refs[$i]));
            }
        }
        $manager->flush();
    }
    
    /**
     * For fixtures that have relations to the same table, they need to appear
     * in the opposite order that they need to be saved.
     * @return boolean 
     */
    public function isReverseSaveOrder(){
        if(!isset($this->file['save_in_reverse']) || $this->file['save_in_reverse'] == false){
            return false;
        }
        return true;
    }
}