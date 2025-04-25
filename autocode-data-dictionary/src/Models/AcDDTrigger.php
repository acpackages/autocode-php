<?php

namespace AcDataDictionary\Models;
require_once 'AcDataDictionary.php';
use AcDataDictionary\Models\AcDataDictionary;
use Autocode\Models\AcJsonBindConfig;
use Autocode\Utils\AcUtilsJson;

class AcDDTrigger {
    const KEY_TRIGGER_NAME = "trigger_name";
    const KEY_TRIGGER_CODE = "trigger_code";
    const KEY_TRIGGER_TABLE_NAME = "table_name";
    const KEY_TRIGGER_EXECUTION = "trigger_execution";
    const KEY_TRIGGER_TABLE_ROW_OPERATION = "row_operation";

    public AcJsonBindConfig $acJsonBindConfig;
    public string $rowOperation = "";
    public string $triggerExecution = "";
    public string $tableName = "";
    public string $triggerName = "";
    public string $triggerCode = "";

    public static function instanceFromJson(array $jsonData): AcDDTrigger {
        $instance = new self();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public static function getInstance(string $triggerName, string $dataDictionaryName = "default"): AcDDTrigger {
        $result = new self();
        $acDataDictionary = AcDataDictionary::getInstance($dataDictionaryName);
        
        if (isset($acDataDictionary->triggers[$triggerName])) {
            $result->fromJson($acDataDictionary->triggers[$triggerName]);
        }
        
        return $result;
    }

    public function __construct() {
        $this->acJsonBindConfig = AcJsonBindConfig::instanceFromJson(jsonData: [
            AcJsonBindConfig::KEY_PROPERY_BINDINGS => [
                self::KEY_TRIGGER_NAME => "triggerName",
                self::KEY_TRIGGER_CODE => "triggerCode",
                self::KEY_TRIGGER_TABLE_NAME => "tableName",
                self::KEY_TRIGGER_EXECUTION => "triggerExecution",
                self::KEY_TRIGGER_TABLE_ROW_OPERATION => "rowOperation"
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
