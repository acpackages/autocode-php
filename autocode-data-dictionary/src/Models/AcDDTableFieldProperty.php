<?php

namespace AcDataDictionary\Models;

class AcDDTableFieldProperty {
    public const KEY_PROPERTY_NAME = "property_name";
    public const KEY_PROPERTY_VALUE = "property_value";

    public string $propertyName = "";
    public mixed $propertyValue = null;

    public static function fromJson(array $jsonData): self {
        $instance = new self();
        $instance->setValuesFromJson($jsonData);
        return $instance;
    }

    public function setValuesFromJson(array $jsonData = []): void {
        if (array_key_exists(self::KEY_PROPERTY_NAME, $jsonData)) {
            $this->propertyName = (string) $jsonData[self::KEY_PROPERTY_NAME];
        }
        if (array_key_exists(self::KEY_PROPERTY_VALUE, $jsonData)) {
            $this->propertyValue = $jsonData[self::KEY_PROPERTY_VALUE];
        }
    }

    public function toJson(): array {
        return [
            self::KEY_PROPERTY_NAME => $this->propertyName,
            self::KEY_PROPERTY_VALUE => $this->propertyValue
        ];
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_THROW_ON_ERROR);
    }
}

?>
