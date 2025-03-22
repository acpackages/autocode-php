<?php

namespace AcDataDictionary\Models;

require_once __DIR__ . './../AcDataDictionary.php';

class AcDDTrigger {
    const KEY_TRIGGER_NAME = "trigger_name";
    const KEY_TRIGGER_CODE = "trigger_code";
    const KEY_TRIGGER_TABLE_NAME = "table_name";
    const KEY_TRIGGER_EXECUTION = "trigger_execution";
    const KEY_TRIGGER_TABLE_ROW_OPERATION = "row_operation";

    public string $rowOperation = "";
    public string $triggerExecution = "";
    public string $tableName = "";
    public string $triggerName = "";
    public string $triggerCode = "";

    public static function fromJson(array $jsonData): AcDDTrigger {
        $instance = new self();
        $instance->setValuesFromJson($jsonData);
        return $instance;
    }

    public static function getInstance(string $triggerName, string $dataDictionaryName = "default"): AcDDTrigger {
        $result = new self();
        $acDataDictionary = AcDataDictionary::getInstance($dataDictionaryName);
        
        if (isset($acDataDictionary->triggers[$triggerName])) {
            $result->setValuesFromJson($acDataDictionary->triggers[$triggerName]);
        }
        
        return $result;
    }

    public function setValuesFromJson(array $jsonData = []): void {
        if (isset($jsonData[self::KEY_TRIGGER_TABLE_ROW_OPERATION])) {
            $this->rowOperation = (string) $jsonData[self::KEY_TRIGGER_TABLE_ROW_OPERATION];
        }
        if (isset($jsonData[self::KEY_TRIGGER_TABLE_NAME])) {
            $this->tableName = (string) $jsonData[self::KEY_TRIGGER_TABLE_NAME];
        }
        if (isset($jsonData[self::KEY_TRIGGER_EXECUTION])) {
            $this->triggerExecution = (string) $jsonData[self::KEY_TRIGGER_EXECUTION];
        }
        if (isset($jsonData[self::KEY_TRIGGER_CODE])) {
            $this->triggerCode = (string) $jsonData[self::KEY_TRIGGER_CODE];
        }
        if (isset($jsonData[self::KEY_TRIGGER_NAME])) {
            $this->triggerName = (string) $jsonData[self::KEY_TRIGGER_NAME];
        }
    }

    public function toJson(): array {
        return [
            self::KEY_TRIGGER_NAME => $this->triggerName,
            self::KEY_TRIGGER_CODE => $this->triggerCode,
            self::KEY_TRIGGER_TABLE_ROW_OPERATION => $this->rowOperation,
            self::KEY_TRIGGER_EXECUTION => $this->triggerExecution,
            self::KEY_TRIGGER_TABLE_NAME => $this->tableName,
        ];
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_THROW_ON_ERROR);
    }
}
