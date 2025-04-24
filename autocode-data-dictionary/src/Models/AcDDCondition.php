<?php

namespace AcDataDictionary\Models;
require_once 'AcDDTableField.php';
require_once 'AcDDTableProperty.php';
require_once 'AcDataDictionary.php';
use Autocode\Models\AcJsonBindConfig;
use Autocode\Utils\AcUtilsJson;

class AcDDCondition {

    const KEY_FIELD = "field";
    const KEY_OPERATOR = "operator";
    const KEY_VALUE = "value";
    public AcJsonBindConfig $acJsonBindConfig;
    public string $field = "";    
    public string $operator = "";
    public mixed $value;

    public static function fromJson(array $jsonData): AcDDCondition {
        $instance = new self();
        $instance->setValuesFromJson($jsonData);
        return $instance;
    }

    public function __construct() {
        $this->acJsonBindConfig = AcJsonBindConfig::fromJson(jsonData: [
            AcJsonBindConfig::KEY_PROPERY_BINDINGS => [
                self::KEY_FIELD => "field",
                self::KEY_OPERATOR => "operator",
                self::KEY_VALUE => "value"
            ]        
        ]);
    }

    public function setValuesFromJson(array $jsonData = []): void {
        AcUtilsJson::bindInstancePropertiesFromJson(instance: $this, data: $jsonData);
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
