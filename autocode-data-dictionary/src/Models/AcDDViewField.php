<?php

namespace AcDataDictionary\Models;
require_once 'AcDDTableFieldProperty.php';
use Autocode\Models\AcJsonBindConfig;
use Autocode\Utils\AcUtilsJson;

class AcDDViewField {
    public const KEY_FIELD_NAME = "field_name";
    public const KEY_FIELD_PROPERTIES = "field_properties";
    public const KEY_FIELD_TYPE = "field_type";
    public const KEY_FIELD_VALUE = "field_value";
    public const KEY_FIELD_SOURCE = "field_source";
    public const KEY_FIELD_SOURCE_NAME = "field_source_name";

    public AcJsonBindConfig $acJsonBindConfig;
    public string $fieldName = "";
    public array $fieldProperties = [];
    public string $fieldType = "text";
    public mixed $fieldValue = null;
    public string $fieldSource = "";
    public string $fieldSourceName = "";

    public static function fromJson(array $jsonData): AcDDViewField {
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
                self::KEY_FIELD_VALUE => "fieldValue",
                self::KEY_FIELD_SOURCE => "fieldSource",
                self::KEY_FIELD_SOURCE_NAME => "fieldSourceName"
            ]        
        ]);  
    }

    public function setValuesFromJson(array $jsonData = []): void {
        if (array_key_exists(self::KEY_FIELD_NAME, $jsonData)) {
            $this->fieldName = (string) $jsonData[self::KEY_FIELD_NAME];
        }
        if (array_key_exists(self::KEY_FIELD_TYPE, $jsonData)) {
            $this->fieldType = (string) $jsonData[self::KEY_FIELD_TYPE];
        }
        if (array_key_exists(self::KEY_FIELD_VALUE, $jsonData)) {
            $this->fieldValue = $jsonData[self::KEY_FIELD_VALUE];
        }
        if (array_key_exists(self::KEY_FIELD_SOURCE, $jsonData)) {
            $this->fieldSource = (string) $jsonData[self::KEY_FIELD_SOURCE];
        }
        if (array_key_exists(self::KEY_FIELD_SOURCE_NAME, $jsonData)) {
            $this->fieldSourceName = (string) $jsonData[self::KEY_FIELD_SOURCE_NAME];
        }
        if (array_key_exists(self::KEY_FIELD_PROPERTIES, $jsonData)) {
            foreach ($jsonData[self::KEY_FIELD_PROPERTIES] as $propertyName => $propertyData) {
                $this->fieldProperties[$propertyName] = AcDDTableFieldProperty::fromJson($propertyData);
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

?>
