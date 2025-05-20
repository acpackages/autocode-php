<?php

namespace AcDataDictionary\Models;
require_once 'AcDDTableColumn.php';
require_once 'AcDDTableProperty.php';
require_once 'AcDataDictionary.php';
use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Utils\AcJsonUtils;

class AcDDCondition {
    const KEY_DATABASE_TYPE = "database_type";
    const KEY_COLUMN_NAME = "column_name";
    const KEY_OPERATOR = "operator";
    const KEY_VALUE = "value";
    
    #[AcBindJsonProperty(key: AcDDCondition::KEY_DATABASE_TYPE)]
    public string $databaseType = "";

    #[AcBindJsonProperty(key: AcDDCondition::KEY_COLUMN_NAME)]
    public string $columnName = "";   
    
    public string $operator = "";

    public mixed $value;

    public static function instanceFromJson(array $jsonData): self {
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
