<?php

namespace AcDataDictionary\Models;

require_once 'AcDDTableField.php';
require_once 'AcDDTableProperty.php';
require_once 'AcDataDictionary.php';
use AcDataDictionary\Models\AcDataDictionary;
use AcDataDictionary\Enums\AcEnumDDTableProperty;
use AcDataDictionary\Models\AcDDTableField;
use AcDataDictionary\Models\AcDDTableProperty;
use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Utils\AcUtilsJson;

class AcDDTable {
    const KEY_TABLE_FIELDS = "table_fields";
    const KEY_TABLE_NAME = "table_name";
    const KEY_TABLE_PROPERTIES = "table_properties";

    #[AcBindJsonProperty(key: AcDDTable::KEY_TABLE_FIELDS)]
    public array $tableFields = [];

    #[AcBindJsonProperty(key: AcDDTable::KEY_TABLE_NAME)]
    public string $tableName = "";    

    #[AcBindJsonProperty(key: AcDDTable::KEY_TABLE_PROPERTIES)]
    public array $tableProperties = [];

    public static function instanceFromJson(array $jsonData): self {
        $instance = new self();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public static function getInstance(string $tableName, string $dataDictionaryName = "default"): self {
        $result = new self();
        $acDataDictionary = AcDataDictionary::getInstance($dataDictionaryName);
        
        if (isset($acDataDictionary->tables[$tableName])) {
            $result->fromJson($acDataDictionary->tables[$tableName]);
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

    public function getSearchQueryFieldNames(): array {
        $result = [];
        foreach ($this->getSearchQueryFields() as $tableField) {
            $result[] = $tableField->fieldName;
        }
        return $result;
    }

    public function getSearchQueryFields(): array {
        $result = [];
        foreach ($this->tableFields as $tableField) {
            if ($tableField->isInSearchQuery()) {
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

    public function fromJson(array $jsonData = []): static {
        if (isset($jsonData[self::KEY_TABLE_FIELDS]) && is_array($jsonData[self::KEY_TABLE_FIELDS])) {
            foreach ($jsonData[self::KEY_TABLE_FIELDS] as $fieldName => $fieldData) {
                $field = AcDDTableField::instanceFromJson($fieldData);
                $field->table = $this;
                $this->tableFields[$fieldName] = $field;
            }
            unset( $jsonData[self::KEY_TABLE_FIELDS]);
        }

        if (isset($jsonData[self::KEY_TABLE_PROPERTIES]) && is_array($jsonData[self::KEY_TABLE_PROPERTIES])) {
            foreach ($jsonData[self::KEY_TABLE_PROPERTIES] as $propertyName => $propertyData) {
                $this->tableProperties[$propertyName] = AcDDTableProperty::instanceFromJson($propertyData);
            }
            unset($jsonData[self::KEY_TABLE_PROPERTIES]);
        }

        AcUtilsJson::setInstancePropertiesFromJsonData(instance: $this, jsonData: $jsonData);
        return $this;
    }

    public function toJson(): array {
        return AcUtilsJson::getJsonDataFromInstance(instance: $this);
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

}
