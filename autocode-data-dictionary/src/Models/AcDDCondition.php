<?php

namespace AcDataDictionary\Models;
require_once 'AcDDTableField.php';
require_once 'AcDDTableProperty.php';
require_once 'AcDataDictionary.php';
use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Utils\AcUtilsJson;

class AcDDCondition {
    const KEY_DATABASE_TYPE = "database_type";
    const KEY_FIELD_NAME = "field_name";
    const KEY_OPERATOR = "operator";
    const KEY_VALUE = "value";
    
    #[AcBindJsonProperty(key: AcDDCondition::KEY_DATABASE_TYPE)]
    public string $databaseType = "";

    #[AcBindJsonProperty(key: AcDDCondition::KEY_FIELD_NAME)]
    public string $fieldName = "";   
    
    public string $operator = "";

    public mixed $value;

    public static function instanceFromJson(array $jsonData): self {
        $instance = new self();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public function fromJson(array $jsonData = []): static {
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
