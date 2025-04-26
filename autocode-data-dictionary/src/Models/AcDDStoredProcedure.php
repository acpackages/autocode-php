<?php

namespace AcDataDictionary\Models;
require_once 'AcDataDictionary.php';
use AcDataDictionary\Models\AcDataDictionary;
use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Utils\AcUtilsJson;

class AcDDStoredProcedure {
    public const KEY_STORED_PROCEDURE_NAME = "stored_procedure_name";
    public const KEY_STORED_PROCEDURE_CODE = "stored_procedure_code";

    #[AcBindJsonProperty(key: AcDDStoredProcedure::KEY_STORED_PROCEDURE_NAME)]
    public string $storedProcedureName = "";

    #[AcBindJsonProperty(key: AcDDStoredProcedure::KEY_STORED_PROCEDURE_CODE)]
    public string $storedProcedureCode = "";

    public static function instanceFromJson(array $jsonData): self {
        $instance = new self();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public static function getInstance(string $storedProcedureName, string $dataDictionaryName = "default"): self {
        $result = new self();
        $acDataDictionary = AcDataDictionary::getInstance($dataDictionaryName);

        if (isset($acDataDictionary->storedProcedures[$storedProcedureName])) {
            $result->fromJson($acDataDictionary->storedProcedures[$storedProcedureName]);
        }

        return $result;
    }

    public function fromJson(array $jsonData = []): static {
        AcUtilsJson::setInstancePropertiesFromJsonData(instance: $this, jsonData: $jsonData);
        return $this;
    }

    public function toJson(): array {
        return AcUtilsJson::getJsonDataFromInstance(instance: $this);
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

}

?>
