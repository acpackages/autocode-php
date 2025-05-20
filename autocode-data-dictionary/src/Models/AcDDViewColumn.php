<?php

namespace AcDataDictionary\Models;
require_once 'AcDDTableColumnProperty.php';
use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Utils\AcJsonUtils;

class AcDDViewColumn {
    public const KEY_COLUMN_NAME = "column_name";
    public const KEY_COLUMN_PROPERTIES = "column_properties";
    public const KEY_COLUMN_TYPE = "column_type";
    public const KEY_COLUMN_VALUE = "column_value";
    public const KEY_COLUMN_SOURCE = "column_source";
    public const KEY_COLUMN_SOURCE_NAME = "column_source_name";

    #[AcBindJsonProperty(key: AcDDViewColumn::KEY_COLUMN_NAME)]
    public string $columnName = "";

    #[AcBindJsonProperty(key: AcDDViewColumn::KEY_COLUMN_PROPERTIES)]
    public array $columnProperties = [];

    #[AcBindJsonProperty(key: AcDDViewColumn::KEY_COLUMN_TYPE)]
    public string $columnType = "text";

    #[AcBindJsonProperty(key: AcDDViewColumn::KEY_COLUMN_VALUE)]
    public mixed $columnValue = null;

    #[AcBindJsonProperty(key: AcDDViewColumn::KEY_COLUMN_SOURCE)]
    public string $columnSource = "";

    #[AcBindJsonProperty(key: AcDDViewColumn::KEY_COLUMN_SOURCE_NAME)]
    public string $columnSourceName = "";

    public static function instanceFromJson(array $jsonData): self {
        $instance = new self();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public function fromJson(array $jsonData = []): static {
        if (array_key_exists(self::KEY_COLUMN_PROPERTIES, $jsonData)) {
            foreach ($jsonData[self::KEY_COLUMN_PROPERTIES] as $propertyName => $propertyData) {
                $this->columnProperties[$propertyName] = AcDDTableColumnProperty::instanceFromJson($propertyData);
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

?>
