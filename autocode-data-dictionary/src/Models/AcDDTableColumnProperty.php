<?php

namespace AcDataDictionary\Models;
use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Utils\AcJsonUtils;

class AcDDTableColumnProperty {
    public const KEY_PROPERTY_NAME = "property_name";
    public const KEY_PROPERTY_VALUE = "property_value";

    #[AcBindJsonProperty(key: AcDDTableColumnProperty::KEY_PROPERTY_NAME)]
    public string $propertyName = "";

    #[AcBindJsonProperty(key: AcDDTableColumnProperty::KEY_PROPERTY_VALUE)]
    public mixed $propertyValue = null;

    public static function instanceFromJson(array $jsonData): static {
        $instance = new self();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public function fromJson(array $jsonData = []): static {
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

?>
