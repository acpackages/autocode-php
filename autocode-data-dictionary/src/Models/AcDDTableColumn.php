<?php

namespace AcDataDictionary\Models;

require_once __DIR__ . './../../../autocode-extensions/vendor/autoload.php';
require_once __DIR__ . './../Enums/AcEnumDDColumnProperty.php';
require_once __DIR__ . './../Enums/AcEnumDDColumnType.php';
require_once 'AcDDRelationship.php';
require_once 'AcDDTable.php';
require_once 'AcDDTableColumnProperty.php';
require_once 'AcDataDictionary.php';
use AcDataDictionary\Enums\AcEnumDDColumnProperty;
use AcDataDictionary\Enums\AcEnumDDColumnType;
use AcDataDictionary\Models\AcDDRelationship;
use AcDataDictionary\Models\AcDDTable;
use AcDataDictionary\Models\AcDDTableColumnProperty;
use AcExtensions\AcExtensionMethods;
use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Utils\AcJsonUtils;

class AcDDTableColumn {
    const KEY_COLUMN_NAME = "column_name";
    const KEY_COLUMN_PROPERTIES = "column_properties";
    const KEY_COLUMN_TYPE = "column_type";
    const KEY_COLUMN_VALUE = "column_value";

    #[AcBindJsonProperty(key: AcDDTableColumn::KEY_COLUMN_NAME)]
    public string $columnName = "";

    #[AcBindJsonProperty(key: AcDDTableColumn::KEY_COLUMN_PROPERTIES)]
    public array $columnProperties = [];

    #[AcBindJsonProperty(key: AcDDTableColumn::KEY_COLUMN_TYPE)]
    public string $columnType = "text";

    #[AcBindJsonProperty(key: AcDDTableColumn::KEY_COLUMN_VALUE)]
    public mixed $columnValue = null;

    public ?AcDDTable $table;

    public static function instanceFromJson(array $jsonData): self {
        $instance = new self();
        $instance->fromJson($jsonData);
        return $instance;
    }
    
    public function checkInAutoNumber(): bool {
        $result = false;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDColumnProperty::CHECK_IN_AUTO_NUMBER,$this->columnProperties)){
            $result = ($this->columnProperties[AcEnumDDColumnProperty::CHECK_IN_AUTO_NUMBER]->propertyValue ?? false) === true;
        }
        return $result;
    }

    public function checkInModify(): bool {
        $result = false;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDColumnProperty::CHECK_IN_MODIFY,$this->columnProperties)){
            $result = ($this->columnProperties[AcEnumDDColumnProperty::CHECK_IN_MODIFY]->propertyValue ?? false) === true;
        }
        return $result;
    }

    public function checkInSave(): bool {
        $result = false;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDColumnProperty::CHECK_IN_SAVE,$this->columnProperties)){
            $result = ($this->columnProperties[AcEnumDDColumnProperty::CHECK_IN_SAVE]->propertyValue ?? false) === true;
        }
        return $result;
    }

    public function getAutoNumberLength(): int {
        $result = 0;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDColumnProperty::AUTO_NUMBER_LENGTH,$this->columnProperties)){
            $result = $this->columnProperties[AcEnumDDColumnProperty::AUTO_NUMBER_LENGTH]->propertyValue;
        }
        return $result;
    }
    
    public function getAutoNumberPrefix(): string {
        $result = "";
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDColumnProperty::AUTO_NUMBER_PREFIX,$this->columnProperties)){
            $result = $this->columnProperties[AcEnumDDColumnProperty::AUTO_NUMBER_PREFIX]->propertyValue;
        }
        return $result;
    }

    public function getAutoNumberPrefixLength(): int {
        $prefix = $this->getAutoNumberPrefix();
        return strlen($prefix);
    }
    
    public function getDefaultValue(): mixed {
        $result = null;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDColumnProperty::DEFAULT_VALUE,$this->columnProperties)){
            $result = $this->columnProperties[AcEnumDDColumnProperty::DEFAULT_VALUE]->propertyValue;
        }
        return $result;
    }

    public function getColumnFormats(): array {
        $result = [];
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDColumnProperty::FORMAT,$this->columnProperties)){
            $result = $this->columnProperties[AcEnumDDColumnProperty::FORMAT]->propertyValue;
        }
        return $result;
    }  
    
    public function getColumnTitle(): string {
        $result = $this->columnName;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDColumnProperty::COLUMN_TITLE,$this->columnProperties)){
            $result = $this->columnProperties[AcEnumDDColumnProperty::COLUMN_TITLE]->propertyValue;
        }
        return $result;
    }  
    
    public function getForeignKeyRelationships(): array {
        return AcDDRelationship::getInstances(destinationTable:$this->table->tableName, destinationColumn:$this->columnName);
    }

    public function getSize(): string {
        $result = 0;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDColumnProperty::SIZE,$this->columnProperties)){
            $result = $this->columnProperties[AcEnumDDColumnProperty::SIZE]->propertyValue;
        }
        return $result;
    }
    
    public function isAutoIncrement(): bool {
        $result = false;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDColumnProperty::AUTO_INCREMENT,$this->columnProperties)){
            $result = ($this->columnProperties[AcEnumDDColumnProperty::AUTO_INCREMENT]->propertyValue ?? false) === true;
        }
        if($this->columnType == AcEnumDDColumnType::AUTO_INCREMENT){
            $result = true;
        }
        return $result;
    }
    
    public function isAutoNumber(): bool {
        $result = false;
        if($this->columnType == AcEnumDDColumnType::AUTO_NUMBER){
            $result = true;
        }
        return $result;
    }

    public function isForeignKey(): bool {
        return count(AcDDRelationship::getInstances(destinationTable:$this->table->tableName, destinationColumn:$this->columnName)) > 0;
    }

    public function isInSearchQuery(): bool {
        $result = false;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDColumnProperty::IN_SEARCH_QUERY,$this->columnProperties)){
            $result = ($this->columnProperties[AcEnumDDColumnProperty::IN_SEARCH_QUERY]->propertyValue ?? false) === true;
        }
        return $result;
    }
    
    public function isNotNull(): bool {
        $result = false;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDColumnProperty::NOT_NULL,$this->columnProperties)){
            $result = ($this->columnProperties[AcEnumDDColumnProperty::NOT_NULL]->propertyValue ?? false) === true;
        }
        return $result;
    }
    
    public function isPrimaryKey(): bool {
        $result = false;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDColumnProperty::PRIMARY_KEY,$this->columnProperties)){
            $result = ($this->columnProperties[AcEnumDDColumnProperty::PRIMARY_KEY]->propertyValue ?? false) === true;
        }
        return $result;
    }

    public function isRequired(): bool {
        $result = false;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDColumnProperty::REQUIRED,$this->columnProperties)){
            $result = ($this->columnProperties[AcEnumDDColumnProperty::REQUIRED]->propertyValue ?? false) === true;
        }
        return $result;
    }

    public function isSelectDistinct(): bool {
        $result = false;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDColumnProperty::IS_SELECT_DISTINCT,$this->columnProperties)){
            $result = ($this->columnProperties[AcEnumDDColumnProperty::IS_SELECT_DISTINCT]->propertyValue ?? false) === true;
        }
        return $result;
    }


    public function isSetValuesNullBeforeDelete(): bool {
        $result = false;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDColumnProperty::SET_NULL_BEFORE_DELETE,$this->columnProperties)){
            $result = ($this->columnProperties[AcEnumDDColumnProperty::SET_NULL_BEFORE_DELETE]->propertyValue ?? false) === true;
        }
        return $result;
    }

    public function isUniqueKey(): bool {
        $result = false;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDColumnProperty::UNIQUE_KEY,$this->columnProperties)){
            $result = ($this->columnProperties[AcEnumDDColumnProperty::UNIQUE_KEY]->propertyValue ?? false) === true;
        }
        return $result;
    }    
    
    public function fromJson(array $jsonData): static {        
        if (isset($jsonData[self::KEY_COLUMN_PROPERTIES]) && is_array($jsonData[self::KEY_COLUMN_PROPERTIES])) {
            foreach ($jsonData[self::KEY_COLUMN_PROPERTIES] as $key => $value) {
                $this->columnProperties[$key] = AcDDTableColumnProperty::instanceFromJson($value);
            }
            unset($jsonData[self::KEY_COLUMN_PROPERTIES]);
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
