<?php

namespace AcDataDictionary\Models;
require_once 'AcDataDictionary.php';
use AcDataDictionary\Models\AcDataDictionary;
use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Utils\AcJsonUtils;

class AcDDTrigger {
    const KEY_ROW_OPERATION = "row_operation";
    const KEY_TABLE_NAME = "table_name";
    const KEY_TRIGGER_CODE = "trigger_code";
    const KEY_TRIGGER_EXECUTION = "trigger_execution";
    const KEY_TRIGGER_NAME = "trigger_name";

    #[AcBindJsonProperty(key: AcDDTrigger::KEY_ROW_OPERATION)]
    public string $rowOperation = "";

    #[AcBindJsonProperty(key: AcDDTrigger::KEY_TRIGGER_EXECUTION)]
    public string $triggerExecution = "";

    #[AcBindJsonProperty(key: AcDDTrigger::KEY_PROPKEY_TABLE_NAMEERTY_NAME)]
    public string $tableName = "";

    #[AcBindJsonProperty(key: AcDDTrigger::KEY_TRIGGER_NAME)]
    public string $triggerName = "";

    #[AcBindJsonProperty(key: AcDDTrigger::KEY_TRIGGER_CODE)]
    public string $triggerCode = "";

    public static function instanceFromJson(array $jsonData): self {
        $instance = new self();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public static function getInstance(string $triggerName, string $dataDictionaryName = "default"): self {
        $result = new self();
        $acDataDictionary = AcDataDictionary::getInstance($dataDictionaryName);
        
        if (isset($acDataDictionary->triggers[$triggerName])) {
            $result->fromJson($acDataDictionary->triggers[$triggerName]);
        }
        
        return $result;
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
