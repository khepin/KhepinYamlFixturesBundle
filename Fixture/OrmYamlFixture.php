<?php

namespace Khepin\YamlFixturesBundle\Fixture;

use Symfony\Component\PropertyAccess\PropertyAccess;

class OrmYamlFixture extends AbstractFixture
{
    public function createObject($class, $data, $metadata, $options = array())
    {
        $mapping = array_keys($metadata->fieldMappings);
        $associations = array_keys($metadata->associationMappings);

        $class = new \ReflectionClass($class);
        $constructArguments = array();
        if (isset($data['__construct'])) {
            $arguments = $data['__construct'];
            if (is_array($arguments)) {
                foreach($arguments as $argument) {
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
            unset($data['__construct']);
        }
        $object = $class->newInstanceArgs($constructArguments);

        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($data as $field => $value) {
            if (in_array($field, $mapping)) {
                // Dates need to be converted to DateTime objects
                $type = $metadata->fieldMappings[$field]['type'];
                if ($type == 'datetime' || $type == 'date' || $type == 'time') {
                    $value = new \DateTime($value);
                }
                $accessor->setValue($object, $field, $value);
            } elseif (in_array($field, $associations)) { // This field is an association
                if (is_array($value)) { // The field is an array of associations
                    $referenceArray = array();
                    foreach ($value as $referenceObject) {
                        $referenceArray[] = $this->loader->getReference($referenceObject);
                    }
                    $accessor->setValue($object, $field, $referenceArray);
                } else {
                    $accessor->setValue($object, $field, $this->loader->getReference($value)); 
                }
            } else {
                $accessor->setValue($object, $field, $value);
            }
        }
        $this->runServiceCalls($object);

        return $object;
    }
}
