<?php

namespace AcDataDictionary\Models;

class AcDDTableRowEvent {
    public const KEY_ABORT = "abort";
    public const KEY_CONDITION = "condition";
    public const KEY_EVENT_TYPE = "event_type";
    public const KEY_NEW_RECORDS = "new_records";
    public const KEY_OLD_RECORDS = "old_records";
    public const KEY_OPERATION = "operation";
    public const KEY_ORIGINAL_CONDITION = "original_condition";
    public const KEY_RESULT = "result";
    public const KEY_TABLE_NAME = "table_name";
    public const KEY_UNIQUE_CONDITION = "unique_condition";
    public const KEY_VALUES = "values";

    public bool $abortOperation = false;
    public string $condition = "";
    public string $eventType = "unknown";
    public array $newRecords = [];
    public array $oldRecords = [];
    public string $operation = "";
    public string $originalCondition = "";
    public array $result = [];
    public string $tableName = "";
    public string $uniqueCondition = "";
    public array $values = [];

    public static function fromJson(array $jsonData): self {
        $instance = new self();
        $instance->setValuesFromJson($jsonData);
        return $instance;
    }

    public function setValuesFromJson(array $jsonData): void {
        $this->abortOperation = $jsonData[self::KEY_ABORT] ?? false;
        $this->condition = $jsonData[self::KEY_CONDITION] ?? "";
        $this->eventType = $jsonData[self::KEY_EVENT_TYPE] ?? "unknown";
        $this->newRecords = $jsonData[self::KEY_NEW_RECORDS] ?? [];
        $this->oldRecords = $jsonData[self::KEY_OLD_RECORDS] ?? [];
        $this->operation = $jsonData[self::KEY_OPERATION] ?? "";
        $this->originalCondition = $jsonData[self::KEY_ORIGINAL_CONDITION] ?? "";
        $this->result = $jsonData[self::KEY_RESULT] ?? [];
        $this->tableName = $jsonData[self::KEY_TABLE_NAME] ?? "";
        $this->uniqueCondition = $jsonData[self::KEY_UNIQUE_CONDITION] ?? "";
        $this->values = $jsonData[self::KEY_VALUES] ?? [];
    }

    public function toJson(): array {
        return [
            self::KEY_ABORT => $this->abortOperation,
            self::KEY_CONDITION => $this->condition,
            self::KEY_EVENT_TYPE => $this->eventType,
            self::KEY_NEW_RECORDS => $this->newRecords,
            self::KEY_OLD_RECORDS => $this->oldRecords,
            self::KEY_OPERATION => $this->operation,
            self::KEY_ORIGINAL_CONDITION => $this->originalCondition,
            self::KEY_RESULT => $this->result,
            self::KEY_TABLE_NAME => $this->tableName,
            self::KEY_UNIQUE_CONDITION => $this->uniqueCondition,
            self::KEY_VALUES => $this->values,
        ];
    }

    public function __toString(): string {
        return json_encode($this->toJson());
    }
}

?>
