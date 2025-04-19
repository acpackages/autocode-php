<?php

namespace ApiDocs\Utils;
require_once __DIR__ ."./../Enums/AcEnumApiDataFormat.php";
require_once __DIR__ ."./../Enums/AcEnumApiDataType.php";
use AcDataDictionary\Enums\AcEnumDDFieldType;
use AcDataDictionary\Models\AcDDTable;
use AcWeb\ApiDocs\Models\AcApiDoc;
use AcWeb\ApiDocs\Models\AcApiDocModel;
use ApiDocs\Enums\AcEnumApiDataFormat;
use ApiDocs\Enums\AcEnumApiDataType;
use ReflectionClass;
use ReflectionEnum;
use ReflectionEnumUnitCase;
use ReflectionNamedType;
use ReflectionProperty;

class AcApiDocUtils
{

    public static function getApiDataFormatFromDataDictionaryDataType(string $dataType)
    {
        $result = "";        
        if(in_array(needle: $dataType, haystack: [AcEnumDDFieldType::AUTO_INCREMENT,AcEnumDDFieldType::INTEGER])){
            $result =AcEnumApiDataFormat::INT64;
        }
        else if($dataType == AcEnumDDFieldType::DOUBLE){
            $result =AcEnumApiDataFormat::DOUBLE;
        }
        else if($dataType == AcEnumDDFieldType::DATE){
            $result =AcEnumApiDataFormat::DATE;
        }
        else if($dataType == AcEnumDDFieldType::DATETIME){
            $result =AcEnumApiDataFormat::DATETIME;
        }
        else if($dataType == AcEnumDDFieldType::PASSWORD){
            $result =AcEnumApiDataFormat::PASSWORD;
        }
        return $result;
    }

    public static function getApiDataTypeFromDataDictionaryDataType(string $dataType)
    {
        $result = AcEnumApiDataType::STRING;        
        if(in_array(needle: $dataType, haystack: [AcEnumDDFieldType::AUTO_INCREMENT,AcEnumDDFieldType::INTEGER])){
            $result =AcEnumApiDataType::INTEGER;
        }
        else if(in_array(needle: $dataType, haystack: [AcEnumDDFieldType::JSON,AcEnumDDFieldType::MEDIA_JSON])){
            $result =AcEnumApiDataType::OBJECT;
        }
        else if($dataType == AcEnumDDFieldType::DOUBLE){
            $result =AcEnumApiDataType::NUMBER;
        }
        return $result;
    }

    public static function getApiModelRefFromAcDDTable(AcDDTable $acDDTable,AcApiDoc &$acApiDoc): array{
        if (isset($acApiDoc->models[$acDDTable->tableName])) {
            return ['$ref' => "#/components/schemas/{$acApiDoc->models[$acDDTable->tableName]->name}"];
        }
        $acApiDocModel = new AcApiDocModel();
        $acApiDocModel->name = $acDDTable->tableName;
        $model = [];
        foreach($acDDTable->tableFields as $field) {
            $fieldType = self::getApiDataTypeFromDataDictionaryDataType($field->fieldType);
            $fieldFormat = self::getApiDataFormatFromDataDictionaryDataType($field->fieldType);
            $model[$field->fieldName] = [
                "type" => $fieldType
            ];
            if($fieldFormat!=""){
                $model[$field->fieldName]["format"] = $fieldFormat;
            }
        }
        $acApiDocModel->properties = $model;        
        $acApiDoc->addModel($acApiDocModel);
        return ['$ref' => "#/components/schemas/{$acApiDoc->models[$acDDTable->tableName]->name}"];
    }

    public static function getApiModelRefFromClass(string $className, AcApiDoc &$acApiDoc): array
    {
        $refClass = new ReflectionClass(objectOrClass: $className);
        $schemaName = $refClass->getShortName();
        if (isset($acApiDoc->models[$schemaName])) {
            return ['$ref' => "#/components/schemas/{$acApiDoc->models[$schemaName]->name}"];
        }
        $acApiDocModel = new AcApiDocModel();
        $acApiDocModel->name = $schemaName;
        $defaults = $refClass->getDefaultProperties();

        foreach ($refClass->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $propName = $prop->getName();
            $type = $prop->getType();
            $propSchema = [];
            $allowsNull = false;
            $types = [];
            if ($type instanceof ReflectionUnionType) {
                foreach ($type->getTypes() as $t) {
                    if ($t->getName() === 'null') {
                        $allowsNull = true;
                    } else {
                        $types[] = $t;
                    }
                }
            } elseif ($type instanceof ReflectionNamedType) {
                $allowsNull = $type->allowsNull();
                $types = [$type];
            }
            if (count($types) === 0) {
                $types[] = new ReflectionNamedType('string', true);
                $allowsNull = true;
            }
            $t = $types[0];
            $name = $t->getName();

            if ($t->isBuiltin()) {
                // Map PHP built‑ins to JSON Schema types
                switch ($name) {
                    case 'int':
                    case 'integer':
                        $propSchema['type'] = 'integer';
                        break;
                    case 'float':
                    case 'double':
                        $propSchema['type'] = 'number';
                        break;
                    case 'bool':
                    case 'boolean':
                        $propSchema['type'] = 'boolean';
                        break;
                    case 'array':
                        $propSchema['type'] = 'array';
                        // Without docblocks we can’t infer item types… leave generic
                        $propSchema['items'] = ['type' => 'object'];
                        break;
                    case 'string':
                    default:
                        $propSchema['type'] = 'string';
                        break;
                }
            } else {
                // It’s a class: either an enum or a nested model
                if (is_subclass_of($name, \UnitEnum::class)) {
                    // PHP 8.1+ enum
                    $reflectionEnum = new ReflectionEnum($name);
                    $backingType = $reflectionEnum->getBackingType();
                    if ($backingType) {
                        // backed enum: use underlying type
                        $btName = $backingType->getName();
                        $propSchema['type'] = match ($btName) {
                            'int', 'integer' => 'integer',
                            'string' => 'string',
                            default => 'string',
                        };
                    } else {
                        // pure enum: treat as string
                        $propSchema['type'] = 'string';
                    }
                    // list out all possible enum values
                    $propSchema['enum'] = array_map(
                        fn(ReflectionEnumUnitCase $c) => $c->getBackingValue() ?? $c->getName(),
                        $reflectionEnum->getCases()
                    );
                } else {
                    // Nested object: recurse and add to components
                    $ref = self::getModelSchemaFromClass(className: $name, acApiDoc: $acApiDoc);
                    $propSchema = $ref;
                }
            }
            if ($allowsNull) {
                $propSchema['nullable'] = true;
            }
            if (array_key_exists($propName, $defaults)) {
                $default = $defaults[$propName];
                // Only JSON‑serializable defaults
                if (
                    is_null($default)
                    || is_scalar($default)
                    || (is_array($default) && json_encode($default) !== false)
                ) {
                    $propSchema['default'] = $default;
                }
            }

            $acApiDocModel->properties[$propName] = $propSchema;
        }

        // Register and return a $ref
        $acApiDoc->addModel(model: $acApiDocModel);
        return ['$ref' => "#/components/schemas/{$schemaName}"];
    }

   

    

    
}
?>