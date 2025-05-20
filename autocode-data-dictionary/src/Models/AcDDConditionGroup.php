<?php

namespace AcDataDictionary\Models;
require_once 'AcDDTableColumn.php';
require_once 'AcDDTableProperty.php';
require_once 'AcDataDictionary.php';
use AcExtensions\AcExtensionMethods;
use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Utils\AcJsonUtils;

class AcDDConditionGroup
{
    const KEY_DATABASE_TYPE = "database_type";
    const KEY_CONDITIONS = "conditions";
    const KEY_OPERATOR = "operator";

    #[AcBindJsonProperty(key: AcDDConditionGroup::KEY_DATABASE_TYPE)]
    public string $databaseType = "";
    public array $conditions = [];
    public string $operator = "";

    public static function instanceFromJson(array $jsonData): self {
        $instance = new self();
        $instance->fromJson(jsonData: $jsonData);
        return $instance;
    }

    public function addCondition(string $columnName, string $operator, mixed $value): static
    {
        $this->conditions[] = AcDDCondition::instanceFromJson(jsonData: [
            AcDDCondition::KEY_COLUMN_NAME => $columnName,
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
                    else if(AcExtensionMethods::arrayContainsKey(key: AcDDCondition::KEY_COLUMN_NAME, array: $condition)){
                        $this->conditions[] = AcDDCondition::instanceFromJson(jsonData: $condition);
                    }
                }
                else{
                    $this->conditions[] = $condition;
                }
            }
            unset($jsonData[self::KEY_CONDITIONS]);
        }
        AcJsonUtils::setInstancePropertiesFromJsonData(instance: $this, jsonData: $jsonData);
        return $this;
    }

    public function toJson(): array {
        return AcJsonUtils::getJsonDataFromInstance(instance: $this);
    }

    public function __toString(): string
    {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }
}
