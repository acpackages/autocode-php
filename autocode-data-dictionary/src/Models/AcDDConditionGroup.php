<?php

namespace AcDataDictionary\Models;
require_once 'AcDDTableField.php';
require_once 'AcDDTableProperty.php';
require_once 'AcDataDictionary.php';
use AcExtensions\AcExtensionMethods;
use Autocode\Models\AcJsonBindConfig;
use Autocode\Utils\AcUtilsJson;

class AcDDConditionGroup
{
    const KEY_CONDITIONS = "conditions";
    const KEY_OPERATOR = "operator";
    public AcJsonBindConfig $acJsonBindConfig;
    public string $databaseType = "";
    public array $conditions = [];
    public string $operator = "";

    public static function instanceFromJson(array $jsonData): AcDDConditionGroup
    {
        $instance = new self();
        $instance->fromJson(jsonData: $jsonData);
        return $instance;
    }

    public function __construct(){
        $this->acJsonBindConfig = AcJsonBindConfig::instanceFromJson(jsonData: [
            AcJsonBindConfig::KEY_PROPERY_BINDINGS => [
                self::KEY_CONDITIONS => "conditions",
                self::KEY_OPERATOR => "operator",
            ]
        ]);
    }

    public function addCondition(string $fieldName, string $operator, mixed $value): static
    {
        $this->conditions[] = AcDDCondition::instanceFromJson(jsonData: [
            AcDDCondition::KEY_FIELD_NAME => $fieldName,
            AcDDCondition::KEY_OPERATOR => $operator,
            AcDDCondition::KEY_VALUE => $value
        ]);
        return $this;
    }

    public function addConditionGroup(array $conditions, ?string $operator = AcEnumDDLogicalOperator::AND): static {
        $this->conditions[] = AcDDConditionGroup::instanceFromJson(jsonData: [
            AcDDConditionGroup::KEY_CONDITIONS => $conditions,
            AcDDConditionGroup::KEY_OPERATOR => $operator
        ]);
        return $this;
    }

    public function fromJson(array $jsonData = []): static {
        if(AcExtensionMethods::arrayContainsKey(key: self::KEY_CONDITIONS, array: $jsonData)) {
            foreach ($jsonData[self::KEY_CONDITIONS] as $condition) {
                if(is_array($condition)){
                    if(AcExtensionMethods::arrayContainsKey(key: self::KEY_CONDITIONS, array: $condition)) {
                        $this->conditions[] = AcDDConditionGroup::instanceFromJson(jsonData: $condition);
                    }
                    else if(AcExtensionMethods::arrayContainsKey(key: AcDDCondition::KEY_FIELD_NAME, array: $condition)){
                        $this->conditions[] = AcDDCondition::instanceFromJson(jsonData: $condition);
                    }
                }
                else{
                    $this->conditions[] = $condition;
                }
            }
            unset($jsonData[self::KEY_CONDITIONS]);
        }
        AcUtilsJson::bindInstancePropertiesFromJson(instance: $this, data: $jsonData);
        return $this;
    }

    public function toJson(): array
    {
        return AcUtilsJson::createJsonArrayFromInstance(instance: $this);
    }

    public function __toString(): string
    {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

    public function toString(): string
    {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }
}
