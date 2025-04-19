<?php

namespace AcDataDictionary\Models;

require_once 'AcDDTableField.php';
require_once 'AcDDTableProperty.php';
require_once 'AcDataDictionary.php';
use AcDataDictionary\Models\AcDataDictionary;
use AcDataDictionary\Enums\AcEnumDDTableProperty;
use AcDataDictionary\Models\AcDDTableField;
use AcDataDictionary\Models\AcDDTableProperty;
use Autocode\Models\AcJsonBindConfig;
use Autocode\Utils\AcUtilsJson;

class AcDDTable {
    const KEY_TABLE_FIELDS = "table_fields";
    const KEY_TABLE_NAME = "table_name";
    const KEY_TABLE_PROPERTIES = "table_properties";

    public AcJsonBindConfig $acJsonBindConfig;
    public string $tableName = "";
    public array $tableFields = []; // Associative array of AcDDTableField
    public array $tableProperties = []; // Associative array of AcDDTableProperty

    public static function fromJson(array $jsonData): AcDDTable {
        $instance = new self();
        $instance->setValuesFromJson($jsonData);
        return $instance;
    }

    public static function getInstance(string $tableName, string $dataDictionaryName = "default"): AcDDTable {
        $result = new self();
        $acDataDictionary = AcDataDictionary::getInstance($dataDictionaryName);
        
        if (isset($acDataDictionary->tables[$tableName])) {
            $result->setValuesFromJson($acDataDictionary->tables[$tableName]);
        }
        
        return $result;
    }

    public function __construct() {
        $this->acJsonBindConfig = AcJsonBindConfig::fromJson(jsonData: [
            AcJsonBindConfig::KEY_PROPERY_BINDINGS => [
                self::KEY_TABLE_FIELDS => "tableFields",
                self::KEY_TABLE_NAME => "tableName",
                self::KEY_TABLE_PROPERTIES => "tableProperties"
            ]        
        ]);
    }

    public function getField(string $fieldName): ?AcDDTableField {
        $result = null;
        foreach($this->tableFields as $field){
            if( $field->fieldName == $fieldName ){
                $result = $field;
            }
        }
        return $result;
    }

    public function getFieldNames(): array {
        $result = [];
        foreach($this->tableFields as $field){
            $result[] = $field->fieldName;
        }
        return $result;
    }

    public function getPrimaryKeyFieldName(): string {
        $result = "";
        $primaryKeyField = $this->getPrimaryKeyField();
        if($primaryKeyField!=null){
            $result = $primaryKeyField->fieldName;
        }
        return $result;
    }

    public function getPrimaryKeyField(): ?AcDDTableField {
        $primaryKeyFields = $this->getPrimaryKeyFields();
        return !empty($primaryKeyFields) ? $primaryKeyFields[0] : null;
    }

    public function getPrimaryKeyFields(): array {
        $result = [];
        foreach ($this->tableFields as $tableField) {
            if ($tableField->isPrimaryKey()) {
                $result[] = $tableField;
            }
        }
        return $result;
    }

    public function getForeignKeyFields(): array {
        $result = [];
        foreach ($this->tableFields as $tableField) {
            if ($tableField->foreignKey) {
                $result[] = $tableField;
            }
        }
        return $result;
    }

    public function getPluralName(): string {
        $result = $this->tableName;
        foreach ($this->tableProperties as $property) {
            if($property->propertyName == AcEnumDDTableProperty::PLURAL_NAME){
                $result = $property->propertyValue;
            }
        }
        return $result;
    }

    public function getSingularName(): string {
        $result = $this->tableName;
        foreach ($this->tableProperties as $property) {
            if($property->propertyName == AcEnumDDTableProperty::SINGULAR_NAME){
                $result = $property->propertyValue;
            }
        }
        return $result;
    }

    public function getSelectDistinctFields(): array {
        $result = [];
        foreach ($this->tableFields as $field) {
            if($field->isSelectDistinct()){
                $result[] = $field;
            }
        }
        return $result;
    }

    public function setValuesFromJson(array $jsonData = []): void {
        if (isset($jsonData[self::KEY_TABLE_NAME])) {
            $this->tableName = (string) $jsonData[self::KEY_TABLE_NAME];
        }

        if (isset($jsonData[self::KEY_TABLE_FIELDS]) && is_array($jsonData[self::KEY_TABLE_FIELDS])) {
            foreach ($jsonData[self::KEY_TABLE_FIELDS] as $fieldName => $fieldData) {
                $field = AcDDTableField::fromJson($fieldData);
                $field->table = $this;
                $this->tableFields[$fieldName] = $field;
            }
        }

        if (isset($jsonData[self::KEY_TABLE_PROPERTIES]) && is_array($jsonData[self::KEY_TABLE_PROPERTIES])) {
            foreach ($jsonData[self::KEY_TABLE_PROPERTIES] as $propertyName => $propertyData) {
                $this->tableProperties[$propertyName] = AcDDTableProperty::fromJson($propertyData);
            }
        }
    }

    public function toJson(): array {
        return AcUtilsJson::createJsonArrayFromInstance(instance: $this);
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

    public function toString():string{
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }
}
