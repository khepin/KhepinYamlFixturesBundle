<?php

namespace Khepin\YamlFixturesBundle\Fixture;

use Doctrine\Common\Util\Inflector;

class MongoYamlFixture extends AbstractFixture {

    /**
     * Creates and returns one object based on the given data and metadata
     *
     * @param $class object's class name
     * @param $data array of the object's fixture data
     * @param $metadata the class metadata for doctrine
     * @param $embedded true for embedded documents
     * @return Object
     */
    public function createObject($class, $data, $metadata, $options =  array()){
        // options to state if a document is to be embedded or persisted on its own
        $embedded = isset($options['embed_one']) ? true : false;
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
                        // This is an embedded document
                        if(isset($metadata->fieldMappings[$field]['embedded']) && $metadata->fieldMappings[$field]['embedded']){
                            $embed_class = $metadata->fieldMappings[$field]['targetDocument'];
                            $embed_data = $value;
                            $embed_meta = $this->getMetaDataForClass($embed_class);
                            $value = $this->createObject($embed_class, $embed_data, $embed_meta, array('embed_one' => true));
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