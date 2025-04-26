<?php

namespace Autocode\Utils;
require_once __DIR__ . "./../Annotations/AcBindJsonProperty.php";
use AcExtensions\AcExtensionMethods;
use Autocode\Annotaions\AcBindJsonProperty;
use ReflectionClass;
use ReflectionObject;
use ReflectionProperty;

class AcUtilsJson
{
    static function getJsonDataFromInstance(object $instance): array
    {
        $result = [];
        $ref = new ReflectionObject($instance);
        foreach ($ref->getProperties() as $property) {
            $propertyName = $property->name;
            $jsonKey = $propertyName;
            $bindJsonAttributeInstance = null;
            $bindJsonAttributes = $property->getAttributes(name: AcBindJsonProperty::class);
            if (!empty($bindJsonAttributes)) {
                $bindJsonAttributeInstance = $bindJsonAttributes[0]->newInstance();
                if($bindJsonAttributeInstance->key != null){
                    $jsonKey = $bindJsonAttributeInstance->key;
                }
            }
            $propertyValue = $property->getValue($instance);
            if ($propertyValue != null) {
                $propertyValue = self::getJsonForPropertyValue(propertyValue: $propertyValue);
                $result[$jsonKey] = $propertyValue;
            }
        }
        return $result;
    }

    static function getJsonForPropertyValue(mixed $propertyValue): mixed
    {
        $result = $propertyValue;
        if (is_object($propertyValue)) {
            $valueRef = new ReflectionObject($propertyValue);
            if ($valueRef->hasMethod("toJson")) {
                $result = $propertyValue->toJson();
            }
        }
        if (is_array($propertyValue)) {
            $propertyValues = [];
            foreach ($propertyValue as $key => $value) {
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
        if ($ref->hasConstant("toJson")) {
            $result = $instance->toJson();
        } else {
            $result = self::getJsonDataFromInstance($instance);
        }
        return $result;
    }

    static function setInstancePropertiesFromJsonData(object &$instance, array $jsonData): object {
        $ref = new ReflectionObject($instance);
        foreach ($ref->getProperties() as $property) {
            self::setInstancePropertyValueFromJson(instance: $instance, property: $property, jsonData: $jsonData);
        }
        return $instance;
    }

    static function setInstancePropertyValueFromJson(object &$instance, ReflectionProperty &$property, array $jsonData): object
    {
        $propertyName = $property->name;
        $jsonKey = $propertyName;
        $bindJsonAttributeInstance = null;
        $bindJsonAttributes = $property->getAttributes(name: AcBindJsonProperty::class);
        if (!empty($bindJsonAttributes)) {
            $bindJsonAttributeInstance = $bindJsonAttributes[0]->newInstance();
            if($bindJsonAttributeInstance->key != null){
                $jsonKey = $bindJsonAttributeInstance->key;
            }
        }
        if(AcExtensionMethods::arrayContainsKey($jsonKey,$jsonData)){
            $value = $jsonData[$jsonKey];
            $propertyType = $property->getType();
            if ($propertyType != null) {
                $propertyClassName = $propertyType->getName();
                if($propertyName == "array" && is_array($value)){
                    if($bindJsonAttributeInstance!=null){
                        if($bindJsonAttributeInstance->arrayType != null){
                            $propertyArrayValue = [];
                            foreach ($value as $key => $arrayValue) {
                                $object = new $bindJsonAttributeInstance->arrayType();
                                AcUtilsJson::setInstancePropertiesFromJsonData(instance: $object, jsonData: $arrayValue);
                                $propertyArrayValue[] = $object;
                            }
                            $value = $propertyArrayValue;
                        }
                    }
                }
                else{
                    if (class_exists($propertyClassName) && is_array(value: $value)) {
                        $object = new $propertyClassName();
                        AcUtilsJson::setInstancePropertiesFromJsonData(instance: $object, jsonData: $value);
                        $value = $object;
                    }
                }                    
            }
            $property->setValue(object: $instance, value: $value);
        }        
        return $instance;
    }
}
?>