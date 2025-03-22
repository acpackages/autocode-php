<?php

namespace AcDataDictionary\Models;

require_once __DIR__ . './../AcDataDictionary.php';

class AcDDStoredProcedure {
    public const KEY_STORED_PROCEDURE_NAME = "stored_procedure_name";
    public const KEY_STORED_PROCEDURE_CODE = "stored_procedure_code";

    public string $storedProcedureName = "";
    public string $storedProcedureCode = "";

    public static function fromJson(array $jsonData): self {
        $instance = new self();
        $instance->setValuesFromJson($jsonData);
        return $instance;
    }

    public static function getInstance(string $storedProcedureName, string $dataDictionaryName = "default"): self {
        $result = new self();
        $acDataDictionary = AcDataDictionary::getInstance($dataDictionaryName);

        if (isset($acDataDictionary->storedProcedures[$storedProcedureName])) {
            $result->setValuesFromJson($acDataDictionary->storedProcedures[$storedProcedureName]);
        }

        return $result;
    }

    public function setValuesFromJson(array $jsonData = []): void {
        if (array_key_exists(self::KEY_STORED_PROCEDURE_NAME, $jsonData)) {
            $this->storedProcedureName = (string) $jsonData[self::KEY_STORED_PROCEDURE_NAME];
        }
        if (array_key_exists(self::KEY_STORED_PROCEDURE_CODE, $jsonData)) {
            $this->storedProcedureCode = (string) $jsonData[self::KEY_STORED_PROCEDURE_CODE];
        }
    }

    public function toJson(): array {
        return [
            self::KEY_STORED_PROCEDURE_NAME => $this->storedProcedureName,
            self::KEY_STORED_PROCEDURE_CODE => $this->storedProcedureCode,
        ];
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_THROW_ON_ERROR);
    }
}

?>
