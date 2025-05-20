<?php

namespace AcDataDictionary\Models;

require_once 'AcDDTableColumn.php';
require_once 'AcDDTableProperty.php';
require_once 'AcDataDictionary.php';
use AcDataDictionary\Models\AcDataDictionary;
use AcDataDictionary\Enums\AcEnumDDTableProperty;
use AcDataDictionary\Models\AcDDTableColumn;
use AcDataDictionary\Models\AcDDTableProperty;
use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Utils\AcJsonUtils;

class AcDDTable {
    const KEY_TABLE_COLUMNS = "table_columns";
    const KEY_TABLE_NAME = "table_name";
    const KEY_TABLE_PROPERTIES = "table_properties";

    #[AcBindJsonProperty(key: AcDDTable::KEY_TABLE_COLUMNS)]
    public array $tableColumns = [];

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

    public function getColumn(string $columnName): ?AcDDTableColumn {
        $result = null;
        foreach($this->tableColumns as $column){
            if( $column->columnName == $columnName ){
                $result = $column;
            }
        }
        return $result;
    }

    public function getColumnNames(): array {
        $result = [];
        foreach($this->tableColumns as $column){
            $result[] = $column->columnName;
        }
        return $result;
    }

    public function getPrimaryKeyColumnName(): string {
        $result = "";
        $primaryKeyColumn = $this->getPrimaryKeyColumn();
        if($primaryKeyColumn!=null){
            $result = $primaryKeyColumn->columnName;
        }
        return $result;
    }

    public function getPrimaryKeyColumn(): ?AcDDTableColumn {
        $primaryKeyColumns = $this->getPrimaryKeyColumns();
        return !empty($primaryKeyColumns) ? $primaryKeyColumns[0] : null;
    }

    public function getPrimaryKeyColumns(): array {
        $result = [];
        foreach ($this->tableColumns as $tableColumn) {
            if ($tableColumn->isPrimaryKey()) {
                $result[] = $tableColumn;
            }
        }
        return $result;
    }

    public function getSearchQueryColumnNames(): array {
        $result = [];
        foreach ($this->getSearchQueryColumns() as $tableColumn) {
            $result[] = $tableColumn->columnName;
        }
        return $result;
    }

    public function getSearchQueryColumns(): array {
        $result = [];
        foreach ($this->tableColumns as $tableColumn) {
            if ($tableColumn->isInSearchQuery()) {
                $result[] = $tableColumn;
            }
        }
        return $result;
    }

    public function getForeignKeyColumns(): array {
        $result = [];
        foreach ($this->tableColumns as $tableColumn) {
            if ($tableColumn->foreignKey) {
                $result[] = $tableColumn;
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

    public function getSelectDistinctColumns(): array {
        $result = [];
        foreach ($this->tableColumns as $column) {
            if($column->isSelectDistinct()){
                $result[] = $column;
            }
        }
        return $result;
    }

    public function fromJson(array $jsonData = []): static {
        if (isset($jsonData[self::KEY_TABLE_COLUMNS]) && is_array($jsonData[self::KEY_TABLE_COLUMNS])) {
            foreach ($jsonData[self::KEY_TABLE_COLUMNS] as $columnName => $columnData) {
                $column = AcDDTableColumn::instanceFromJson($columnData);
                $column->table = $this;
                $this->tableColumns[$columnName] = $column;
            }
            unset( $jsonData[self::KEY_TABLE_COLUMNS]);
        }

        if (isset($jsonData[self::KEY_TABLE_PROPERTIES]) && is_array($jsonData[self::KEY_TABLE_PROPERTIES])) {
            foreach ($jsonData[self::KEY_TABLE_PROPERTIES] as $propertyName => $propertyData) {
                $this->tableProperties[$propertyName] = AcDDTableProperty::instanceFromJson($propertyData);
            }
            unset($jsonData[self::KEY_TABLE_PROPERTIES]);
        }

        AcJsonUtils::setInstancePropertiesFromJsonData(instance: $this, jsonData: $jsonData);
        return $this;
    }

    public function toJson(): array {
        return AcJsonUtils::getJsonDataFromInstance(instance: $this);
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

}
