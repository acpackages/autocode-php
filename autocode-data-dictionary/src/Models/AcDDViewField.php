<?php

namespace AcDataDictionary\Models;

require_once 'AcDDTableFieldProperty.php';

class AcDDViewField {
    public const KEY_FIELD_NAME = "field_name";
    public const KEY_FIELD_PROPERTIES = "field_properties";
    public const KEY_FIELD_TYPE = "field_type";
    public const KEY_FIELD_VALUE = "field_value";
    public const KEY_FIELD_SOURCE = "field_source";
    public const KEY_FIELD_SOURCE_NAME = "field_source_name";

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
        $result = [
            self::KEY_FIELD_NAME => $this->fieldName,
            self::KEY_FIELD_TYPE => $this->fieldType,
            self::KEY_FIELD_VALUE => $this->fieldValue,
            self::KEY_FIELD_SOURCE => $this->fieldSource,
            self::KEY_FIELD_SOURCE_NAME => $this->fieldSourceName,
            self::KEY_FIELD_PROPERTIES => [],
        ];
        foreach ($this->fieldProperties as $propertyName => $property) {
            $result[self::KEY_FIELD_PROPERTIES][$propertyName] = $property->toJson();
        }
        return $result;
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_UNESCAPED_UNICODE);
    }
}

?>
