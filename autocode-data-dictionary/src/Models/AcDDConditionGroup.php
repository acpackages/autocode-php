<?php

namespace AcDataDictionary\Models;
require_once 'AcDDTableField.php';
require_once 'AcDDTableProperty.php';
require_once 'AcDataDictionary.php';
use Autocode\Models\AcJsonBindConfig;
use Autocode\Utils\AcUtilsJson;

class AcDDConditionGroup {
    const KEY_CONDITIONS = "conditions";
    const KEY_OPERATOR = "operator";
    public AcJsonBindConfig $acJsonBindConfig;
    public array $conditions = []; 
    public string $operator = "";

    public static function fromJson(array $jsonData): AcDDConditionGroup {
        $instance = new self();
        $instance->setValuesFromJson(jsonData: $jsonData);
        return $instance;
    }

    public function __construct() {
        $this->acJsonBindConfig = AcJsonBindConfig::fromJson(jsonData: [
            AcJsonBindConfig::KEY_PROPERY_BINDINGS => [
                self::KEY_CONDITIONS => "conditions",
                self::KEY_OPERATOR => "operator",
            ]        
        ]);
    }

    public function addCondition(string $field,string $operator,mixed $value): void {
        $this->conditions[] = AcDDCondition::fromJson(jsonData: [
            AcDDConditionGroup::KEY_CONDITIONS => $field,
            AcDDConditionGroup::KEY_OPERATOR => $operator
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
