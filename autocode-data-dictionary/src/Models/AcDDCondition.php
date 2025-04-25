<?php

namespace AcDataDictionary\Models;
require_once 'AcDDTableField.php';
require_once 'AcDDTableProperty.php';
require_once 'AcDataDictionary.php';
use Autocode\Models\AcJsonBindConfig;
use Autocode\Utils\AcUtilsJson;

class AcDDCondition {

    const KEY_FIELD_NAME = "field_name";
    const KEY_OPERATOR = "operator";
    const KEY_VALUE = "value";
    public AcJsonBindConfig $acJsonBindConfig;
    public string $databaseType = "";
    public string $fieldName = "";    
    public string $operator = "";
    public mixed $value;

    public static function instanceFromJson(array $jsonData): AcDDCondition {
        $instance = new self();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public function __construct() {
        $this->acJsonBindConfig = AcJsonBindConfig::instanceFromJson(jsonData: [
            AcJsonBindConfig::KEY_PROPERY_BINDINGS => [
                self::KEY_FIELD_NAME => "fieldName",
                self::KEY_OPERATOR => "operator",
                self::KEY_VALUE => "value"
            ]        
        ]);
    }

    public function fromJson(array $jsonData = []): static {
        AcUtilsJson::bindInstancePropertiesFromJson(instance: $this, data: $jsonData);
        return $this;
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
