<?php

namespace Khepin\YamlFixturesBundle\Fixture;

use Doctrine\Common\Util\Inflector;

class OrmYamlFixture extends AbstractFixture
{
    public function createObject($class, $data, $metadata, $options = array())
    {
        $mapping = array_keys($metadata->fieldMappings);
        $associations = array_keys($metadata->associationMappings);

        $class = new \ReflectionClass($class);
        $constructArguments = [];
        if (isset($data['__construct'])) {
            $constructArguments = $this->constructorArgs($data['__construct']);
            unset($data['__construct']);
        }
        $object = $class->newInstanceArgs($constructArguments);

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
                if (is_array($value)) { // The field is an array of associations
                    $referenceArray = array();
                    foreach ($value as $referenceObject) {
                        $referenceArray[] = $this->loader->getReference($referenceObject);
                    }
                    $object->$method($referenceArray);
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
