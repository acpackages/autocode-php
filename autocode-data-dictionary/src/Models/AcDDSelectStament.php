<?php

namespace AcDataDictionary\Models;

require_once __DIR__ . './../AcDataDictionary.php';
require_once 'AcDDTableField.php';
require_once 'AcDDTableProperty.php';
use AcDataDictionary\AcDataDictionary;
use AcDataDictionary\Models\AcDDTableField;
use AcDataDictionary\Models\AcDDTableProperty;

class AcDDSelectStatement {

    const KEY_EXCLUDE_FIELDS = "exclude_fields";
    const KEY_INCLUDE_FIELDS = "include_fields";
    const KEY_FILTERS = "filters";

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
        $result = [
            self::KEY_TABLE_NAME => $this->tableName,
            self::KEY_TABLE_FIELDS => [],
            self::KEY_TABLE_PROPERTIES => [],
        ];

        foreach ($this->tableFields as $fieldName => $field) {
            $result[self::KEY_TABLE_FIELDS][$fieldName] = $field->toJson();
        }

        foreach ($this->tableProperties as $propertyName => $property) {
            $result[self::KEY_TABLE_PROPERTIES][$propertyName] = $property->toJson();
        }

        return $result;
    }

    public function __toString(): string {
        return json_encode($this->toJson());
    }
}
