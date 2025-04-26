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
use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Utils\AcUtilsJson;

class AcDDTableField {
    const KEY_FIELD_NAME = "field_name";
    const KEY_FIELD_PROPERTIES = "field_properties";
    const KEY_FIELD_TYPE = "field_type";
    const KEY_FIELD_VALUE = "field_value";

    #[AcBindJsonProperty(key: AcDDTableField::KEY_FIELD_NAME)]
    public string $fieldName = "";

    #[AcBindJsonProperty(key: AcDDTableField::KEY_FIELD_PROPERTIES)]
    public array $fieldProperties = [];

    #[AcBindJsonProperty(key: AcDDTableField::KEY_FIELD_TYPE)]
    public string $fieldType = "text";

    #[AcBindJsonProperty(key: AcDDTableField::KEY_FIELD_VALUE)]
    public mixed $fieldValue = null;

    public ?AcDDTable $table;

    public static function instanceFromJson(array $jsonData): self {
        $instance = new self();
        $instance->fromJson($jsonData);
        return $instance;
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
    
    public function fromJson(array $jsonData): static {        
        if (isset($jsonData[self::KEY_FIELD_PROPERTIES]) && is_array($jsonData[self::KEY_FIELD_PROPERTIES])) {
            foreach ($jsonData[self::KEY_FIELD_PROPERTIES] as $key => $value) {
                $this->fieldProperties[$key] = AcDDTableFieldProperty::instanceFromJson($value);
            }
            unset($jsonData[self::KEY_FIELD_PROPERTIES]);
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
