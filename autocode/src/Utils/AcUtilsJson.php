<?php

namespace Autocode\Utils;

use ReflectionClass;
use ReflectionObject;
use ReflectionProperty;

class AcUtilsJson
{
    
    static function bindInstancePropertiesFromJson(object &$instance,array $data)
    {
        $ref = new ReflectionObject($instance);
        $jsonBindings = [];
        $hasJsonBindings = false;
        if($ref->hasProperty(name: "acJsonBindConfig")){
            $jsonBindProperty = $ref->getProperty(name: "acJsonBindConfig");
            $jsonBindConfig = $jsonBindProperty->getValue($instance);
            $jsonBindings = $jsonBindConfig->propertyBindings;
            $hasJsonBindings = true;
        }
        foreach($data as $key => $value){
            $property = null;
            if($hasJsonBindings){
                if(isset($jsonBindings[$key])){
                    if($ref->hasProperty(name: $jsonBindings[$key])){
                        $property = $ref->getProperty(name: $jsonBindings[$key]);
                    }
                }
            }
            else{
                if($ref->hasProperty($key)){
                    $property = $ref->getProperty(name: $key);
                }
            }
            if($property!=null){
                $property->setValue($instance, $data[$key]);
            }
        }
        return $instance;
    }

    static function createJsonArrayFromInstance(object $instance): array
    {
        $result = [];
        $ref = new ReflectionObject($instance);
        $jsonBindings = [];
        $hasJsonBindings = false;
        if($ref->hasProperty(name: "acJsonBindConfig")){
            $jsonBindProperty = $ref->getProperty(name: "acJsonBindConfig");
            $jsonBindConfig = $jsonBindProperty->getValue($instance);
            foreach ($jsonBindConfig->propertyBindings as $key => $propertyName) {
                $jsonBindings[$propertyName] = $key;
            }
            $hasJsonBindings = true;
        }
        foreach($ref->getProperties() as $property){
            $propertyName = $property->name;
            $propertyValue = $property->getValue($instance);
            if($propertyValue != null){
                if($hasJsonBindings){
                    if(isset($jsonBindings[$propertyName])){
                        $result[$jsonBindings[$propertyName]] = $propertyValue;
                    }
                }
                else{
                    if($ref->hasProperty($key)){
                        $result[$propertyName] = $propertyValue;
                    }
                }
            }            
        }
        return $result;
    }

    static function instanceToJson(object $instance): array
    {
        $result = [];
        $ref = new ReflectionObject($instance);
        if($ref->hasConstant("toJson")){
            $result = $instance->toJson();
        }
        else{
            $result = self::createJsonArrayFromInstance($instance);
        }
        return $result;
    }
}
?>