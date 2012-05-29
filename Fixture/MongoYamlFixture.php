<?php

namespace Khepin\YamlFixturesBundle\Fixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\Inflector;

class MongoYamlFixture extends AbstractFixture {

    public function load(ObjectManager $manager, $tags = null) {
        if(!$this->hasTag($tags)){
            return;
        }
        $class = $this->file['model'];
        // Get the fields that are not "associations"
        $metadata = $this->getMetaDataForClass($class);

        foreach ($this->file['fixtures'] as $reference => $fixture_data) {
            $object = $this->createObject($class, $fixture_data, $metadata);
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

    public function getMetadataForClass($class){
        $manager = $this->loader->getManager('mongodb');
        $cmf = $manager->getMetadataFactory();

        return $cmf->getMetaDataFor($class);
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

    public function createObject($class, $data, $metadata, $embedded = false){
        $mapping = array_keys($metadata->fieldMappings);
        // Instantiate new object
        $object = new $class;
        foreach ($data as $field => $value) {
            // Add the fields defined in the fixtures file
            $method = Inflector::camelize('set_' . $field);
            // This is a standard field
            if (in_array($field, $mapping)) {
                // Dates need to be converted to DateTime objects
                $type = $metadata->fieldMappings[$field]['type'];

                if($type == 'many'){
                    $method = Inflector::camelize('add_'.$field);
                    foreach($value as $reference_object){
                        $object->$method($this->loader->getReference($reference_object));
                    }
                } else {
                    if ($type == 'datetime' OR $type == 'date') {
                        $value = new \DateTime($value);
                    }
                    if($type == 'one'){
                        if(isset($metadata->fieldMappings[$field]['embedded']) && $metadata->fieldMappings[$field]['embedded']){
                            $embed_class = $metadata->fieldMappings[$field]['targetDocument'];
                            $embed_data = $value;
                            $embed_meta = $this->getMetaDataForClass($embed_class);
                            $value = $this->createObject($embed_class, $embed_data, $embed_meta, true);
                        } else {
                            $value = $this->loader->getReference($value);
                        }
                    }
                    $object->$method($value);
                }
            } else {
                // The key is not a field's name but the name of a method to be called
                $object->$method($value);
            }
        }
        // Save a reference to the current object
        if(!$embedded){
            $this->runServiceCalls($object);
        }
        
        return $object;
    }
}