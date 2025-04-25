<?php

namespace Autocode\Utils;
require_once __DIR__ ."./../Annotations/AcBindJsonProperty.php";
use Autocode\Annotaions\AcBindJsonProperty;
use ReflectionClass;
use ReflectionObject;
use ReflectionProperty;

class AcUtilsJson
{
    
    static function bindInstancePropertiesFromJson(object &$instance,array $data)
    {
        $ref = new ReflectionObject($instance);
        $jsonBindings = [];
        foreach($ref->getProperties() as $property){
            $propertyName = $property->name;
            $jsonKey = $propertyName;
            $bindJsonAttributes = $property->getAttributes(name:AcBindJsonProperty::class);
            if(!empty($bindJsonAttributes)){
                $jsonKey = $bindJsonAttributes[0]->newInstance()->key;
            } 
            $jsonBindings[$jsonKey] = $propertyName;                    
        }
        foreach($data as $key => $value){
            $property = null;
            if(isset($jsonBindings[$key])){
                $propertyName = $jsonBindings[$key];
                if($ref->hasProperty(name: $propertyName)){
                    $property = $ref->getProperty(name: $propertyName);
                    $property->setValue($instance, $data[$key]);
                }
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
            $jsonKey = $propertyName;
            if($hasJsonBindings){
                if(isset($jsonBindings[$propertyName])){
                    $jsonKey = $jsonBindings[$propertyName];
                }
            }
            $bindJsonAttributes = $property->getAttributes(name:AcBindJsonProperty::class);
            if(!empty($bindJsonAttributes)){
                $jsonKey = $bindJsonAttributes[0]->newInstance()->key;
            }
            if($propertyName!="acJsonBindConfig"){
                $propertyValue = $property->getValue($instance);
                if($propertyValue != null){
                    $propertyValue = self::getJsonForPropertValue(propertyValue:$propertyValue);
                    $result[$jsonKey] = $propertyValue;
                } 
            }                       
        }
        return $result;
    }

    static function getJsonForPropertValue(mixed $propertyValue): mixed{
        $result = $propertyValue;
        if(is_object($propertyValue)){
            $valueRef = new ReflectionObject($propertyValue);
            if($valueRef->hasMethod("toJson")){
                $result = $propertyValue->toJson();
            }
        }
        if(is_array($propertyValue)){
            $propertyValues = [];
            foreach($propertyValue as $key=>$value){
                $propertyValues[$key] = self::getJsonForPropertValue(propertyValue: $value);
            }
            $result = $propertyValues;
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