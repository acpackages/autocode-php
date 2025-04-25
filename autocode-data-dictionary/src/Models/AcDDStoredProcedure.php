<?php

namespace AcDataDictionary\Models;
require_once 'AcDataDictionary.php';
use AcDataDictionary\Models\AcDataDictionary;
use Autocode\Models\AcJsonBindConfig;
use Autocode\Utils\AcUtilsJson;

class AcDDStoredProcedure {
    public const KEY_STORED_PROCEDURE_NAME = "stored_procedure_name";
    public const KEY_STORED_PROCEDURE_CODE = "stored_procedure_code";

    public AcJsonBindConfig $acJsonBindConfig;
    public string $storedProcedureName = "";
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

    public function __construct() {
        $this->acJsonBindConfig = AcJsonBindConfig::instanceFromJson(jsonData: [
            AcJsonBindConfig::KEY_PROPERY_BINDINGS => [
                self::KEY_STORED_PROCEDURE_CODE => "storedProcedureCode",
                self::KEY_STORED_PROCEDURE_NAME => "storedProcedureName"
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

?>
