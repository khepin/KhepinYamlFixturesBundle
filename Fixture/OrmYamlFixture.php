<?php

namespace Khepin\YamlFixturesBundle\Fixture;

use Doctrine\Common\Util\Inflector;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class OrmYamlFixture extends AbstractFixture
{
    public function createObject($class, $data, $metadata, $options = array())
    {
        $mapping = array_keys($metadata->fieldMappings);
        $associations = array_keys($metadata->associationMappings);

        $object = new $class;
        foreach ($data as $field => $value) {
            // Add the fields defined in the fistures file
            $method = Inflector::camelize('set_' . $field);
            //
            if (in_array($field, $mapping)) {
                // Dates need to be converted to DateTime objects
                $type = $metadata->fieldMappings[$field]['type'];
                if ($type == 'datetime' || $type == 'date' || $type == 'time') {
                    $value = new \DateTime($value);
                }
                $object->$method($value);
            } elseif (in_array($field, $associations)) { // This field is an association
                if (is_array($value)) {

                    if ($metadata->associationMappings[$field]['type'] == ClassMetadataInfo::ONE_TO_ONE &&
                        isset($value['_model']) &&
                        isset($value['_fixture'])
                    ) {
                        // The field is a embedded one to one association
                       $oneToOneObject = $this->createObject($value['_model'], $value['_fixture'], $this->getMetadataForClass($value['_model']));
                       $object->$method($oneToOneObject);
                    } else {
                        // The field is an array of associations
                        $referenceArray = array();
                        foreach ($value as $referenceObject) {
                            $referenceArray[] = $this->loader->getReference($referenceObject);
                        }
                        $object->$method($referenceArray);
                    }
                } else {
                    $object->$method($this->loader->getReference($value));
                }
            } else {
                // It's a method call that will set a field named differently
                // eg: FOSUserBundle ->setPlainPassword sets the password after
                // Encrypting it
                $object->$method($value);
            }
        }
        $this->runServiceCalls($object);

        return $object;
    }
}
