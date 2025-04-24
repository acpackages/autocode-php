<?php

namespace AcDataDictionary\Models;

require_once __DIR__ . './../../../autocode-extensions/vendor/autoload.php';
require_once __DIR__ . './../Enums/AcEnumDDFieldProperty.php';
require_once __DIR__ . './../Enums/AcEnumDDFieldType.php';
require_once 'AcDDRelationship.php';
require_once 'AcDDTable.php';
require_once 'AcDDTableFieldProperty.php';
require_once 'AcDataDictionary.php';
use AcDataDictionary\Models\AcDataDictionary;
use AcDataDictionary\Enums\AcEnumDDFieldProperty;
use AcDataDictionary\Enums\AcEnumDDFieldType;
use AcDataDictionary\Models\AcDDRelationship;
use AcDataDictionary\Models\AcDDTable;
use AcDataDictionary\Models\AcDDTableFieldProperty;
use AcExtensions\AcExtensionMethods;
use Autocode\Models\AcJsonBindConfig;
use Autocode\Utils\AcUtilsJson;

class AcDDTableField {
    const KEY_FIELD_NAME = "field_name";
    const KEY_FIELD_PROPERTIES = "field_properties";
    const KEY_FIELD_TYPE = "field_type";
    const KEY_FIELD_VALUE = "field_value";

    public AcJsonBindConfig $acJsonBindConfig;
    public string $fieldName = "";
    public array $fieldProperties = [];
    public string $fieldType = "text";
    public mixed $fieldValue = null;
    public ?AcDDTable $table;

    public static function fromJson(array $jsonData): AcDDTableField {
        $instance = new self();
        $instance->setValuesFromJson($jsonData);
        return $instance;
    }

    public function __construct() {
        $this->acJsonBindConfig = AcJsonBindConfig::fromJson(jsonData: [
            AcJsonBindConfig::KEY_PROPERY_BINDINGS => [
                self::KEY_FIELD_NAME => "fieldName",
                self::KEY_FIELD_PROPERTIES => "fieldProperties",
                self::KEY_FIELD_TYPE => "fieldType",
                self::KEY_FIELD_VALUE => "fieldValue"
            ]        
        ]);
        $this->table = new AcDDTable();        
    }
    
    public function checkInAutoNumber(): bool {
        $result = false;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDFieldProperty::CHECK_IN_AUTO_NUMBER,$this->fieldProperties)){
            $result = ($this->fieldProperties[AcEnumDDFieldProperty::CHECK_IN_AUTO_NUMBER]->propertyValue ?? false) === true;
        }
        return $result;
    }

    public function checkInModify(): bool {
        $result = false;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDFieldProperty::CHECK_IN_MODIFY,$this->fieldProperties)){
            $result = ($this->fieldProperties[AcEnumDDFieldProperty::CHECK_IN_MODIFY]->propertyValue ?? false) === true;
        }
        return $result;
    }

    public function checkInSave(): bool {
        $result = false;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDFieldProperty::CHECK_IN_SAVE,$this->fieldProperties)){
            $result = ($this->fieldProperties[AcEnumDDFieldProperty::CHECK_IN_SAVE]->propertyValue ?? false) === true;
        }
        return $result;
    }

    public function getAutoNumberLength(): int {
        $result = 0;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDFieldProperty::AUTO_NUMBER_LENGTH,$this->fieldProperties)){
            $result = $this->fieldProperties[AcEnumDDFieldProperty::AUTO_NUMBER_LENGTH]->propertyValue;
        }
        return $result;
    }
    
    public function getAutoNumberPrefix(): string {
        $result = "";
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDFieldProperty::AUTO_NUMBER_PREFIX,$this->fieldProperties)){
            $result = $this->fieldProperties[AcEnumDDFieldProperty::AUTO_NUMBER_PREFIX]->propertyValue;
        }
        return $result;
    }

    public function getAutoNumberPrefixLength(): int {
        $prefix = $this->getAutoNumberPrefix();
        return strlen($prefix);
    }
    
    public function getDefaultValue(): mixed {
        $result = null;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDFieldProperty::DEFAULT_VALUE,$this->fieldProperties)){
            $result = $this->fieldProperties[AcEnumDDFieldProperty::DEFAULT_VALUE]->propertyValue;
        }
        return $result;
    }

    public function getFieldFormats(): array {
        $result = [];
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDFieldProperty::FORMAT,$this->fieldProperties)){
            $result = $this->fieldProperties[AcEnumDDFieldProperty::FORMAT]->propertyValue;
        }
        return $result;
    }  
    
    public function getFieldTitle(): string {
        $result = $this->fieldName;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDFieldProperty::FIELD_TITLE,$this->fieldProperties)){
            $result = $this->fieldProperties[AcEnumDDFieldProperty::FIELD_TITLE]->propertyValue;
        }
        return $result;
    }  
    
    public function getForeignKeyRelationships(): array {
        return AcDDRelationship::getInstances(destinationTable:$this->table->tableName, destinationField:$this->fieldName);
    }

    public function getSize(): string {
        $result = 0;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDFieldProperty::SIZE,$this->fieldProperties)){
            $result = $this->fieldProperties[AcEnumDDFieldProperty::SIZE]->propertyValue;
        }
        return $result;
    }
    
    public function isAutoIncrement(): bool {
        $result = false;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDFieldProperty::AUTO_INCREMENT,$this->fieldProperties)){
            $result = ($this->fieldProperties[AcEnumDDFieldProperty::AUTO_INCREMENT]->propertyValue ?? false) === true;
        }
        if($this->fieldType == AcEnumDDFieldType::AUTO_INCREMENT){
            $result = true;
        }
        return $result;
    }
    
    public function isAutoNumber(): bool {
        $result = false;
        if($this->fieldType == AcEnumDDFieldType::AUTO_NUMBER){
            $result = true;
        }
        return $result;
    }

    public function isForeignKey(): bool {
        return count(AcDDRelationship::getInstances(destinationTable:$this->table->tableName, destinationField:$this->fieldName)) > 0;
    }

    public function isInSearchQuery(): bool {
        $result = false;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDFieldProperty::IN_SEARCH_QUERY,$this->fieldProperties)){
            $result = ($this->fieldProperties[AcEnumDDFieldProperty::IN_SEARCH_QUERY]->propertyValue ?? false) === true;
        }
        return $result;
    }
    
    public function isNotNull(): bool {
        $result = false;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDFieldProperty::NOT_NULL,$this->fieldProperties)){
            $result = ($this->fieldProperties[AcEnumDDFieldProperty::NOT_NULL]->propertyValue ?? false) === true;
        }
        return $result;
    }
    
    public function isPrimaryKey(): bool {
        $result = false;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDFieldProperty::PRIMARY_KEY,$this->fieldProperties)){
            $result = ($this->fieldProperties[AcEnumDDFieldProperty::PRIMARY_KEY]->propertyValue ?? false) === true;
        }
        return $result;
    }

    public function isRequired(): bool {
        $result = false;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDFieldProperty::REQUIRED,$this->fieldProperties)){
            $result = ($this->fieldProperties[AcEnumDDFieldProperty::REQUIRED]->propertyValue ?? false) === true;
        }
        return $result;
    }

    public function isSelectDistinct(): bool {
        $result = false;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDFieldProperty::IS_SELECT_DISTINCT,$this->fieldProperties)){
            $result = ($this->fieldProperties[AcEnumDDFieldProperty::IS_SELECT_DISTINCT]->propertyValue ?? false) === true;
        }
        return $result;
    }


    public function isSetValuesNullBeforeDelete(): bool {
        $result = false;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDFieldProperty::SET_NULL_BEFORE_DELETE,$this->fieldProperties)){
            $result = ($this->fieldProperties[AcEnumDDFieldProperty::SET_NULL_BEFORE_DELETE]->propertyValue ?? false) === true;
        }
        return $result;
    }

    public function isUniqueKey(): bool {
        $result = false;
        if(AcExtensionMethods::arrayContainsKey(AcEnumDDFieldProperty::UNIQUE_KEY,$this->fieldProperties)){
            $result = ($this->fieldProperties[AcEnumDDFieldProperty::UNIQUE_KEY]->propertyValue ?? false) === true;
        }
        return $result;
    }    
    
    public function setValuesFromJson(array $jsonData): void {
        $this->fieldName = $jsonData[self::KEY_FIELD_NAME] ?? "";
        $this->fieldType = $jsonData[self::KEY_FIELD_TYPE] ?? "text";
        $this->fieldValue = $jsonData[self::KEY_FIELD_VALUE] ?? null;
        
        if (isset($jsonData[self::KEY_FIELD_PROPERTIES]) && is_array($jsonData[self::KEY_FIELD_PROPERTIES])) {
            foreach ($jsonData[self::KEY_FIELD_PROPERTIES] as $key => $value) {
                $this->fieldProperties[$key] = AcDDTableFieldProperty::fromJson($value);
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
