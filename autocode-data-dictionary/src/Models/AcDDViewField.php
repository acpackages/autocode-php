<?php

namespace AcDataDictionary\Models;
require_once 'AcDDTableFieldProperty.php';
use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Utils\AcUtilsJson;

class AcDDViewField {
    public const KEY_FIELD_NAME = "field_name";
    public const KEY_FIELD_PROPERTIES = "field_properties";
    public const KEY_FIELD_TYPE = "field_type";
    public const KEY_FIELD_VALUE = "field_value";
    public const KEY_FIELD_SOURCE = "field_source";
    public const KEY_FIELD_SOURCE_NAME = "field_source_name";

    #[AcBindJsonProperty(key: AcDDViewField::KEY_FIELD_NAME)]
    public string $fieldName = "";

    #[AcBindJsonProperty(key: AcDDViewField::KEY_FIELD_PROPERTIES)]
    public array $fieldProperties = [];

    #[AcBindJsonProperty(key: AcDDViewField::KEY_FIELD_TYPE)]
    public string $fieldType = "text";

    #[AcBindJsonProperty(key: AcDDViewField::KEY_FIELD_VALUE)]
    public mixed $fieldValue = null;

    #[AcBindJsonProperty(key: AcDDViewField::KEY_FIELD_SOURCE)]
    public string $fieldSource = "";

    #[AcBindJsonProperty(key: AcDDViewField::KEY_FIELD_SOURCE_NAME)]
    public string $fieldSourceName = "";

    public static function instanceFromJson(array $jsonData): self {
        $instance = new self();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public function fromJson(array $jsonData = []): static {
        if (array_key_exists(self::KEY_FIELD_PROPERTIES, $jsonData)) {
            foreach ($jsonData[self::KEY_FIELD_PROPERTIES] as $propertyName => $propertyData) {
                $this->fieldProperties[$propertyName] = AcDDTableFieldProperty::instanceFromJson($propertyData);
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

?>
