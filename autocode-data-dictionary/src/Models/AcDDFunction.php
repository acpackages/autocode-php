<?php

namespace AcDataDictionary\Models;
require_once 'AcDataDictionary.php';
use AcDataDictionary\Models\AcDataDictionary;
use Autocode\Models\AcJsonBindConfig;
use Autocode\Utils\AcUtilsJson;


class AcDDFunction {
    public const KEY_FUNCTION_NAME = "function_name";
    public const KEY_FUNCTION_CODE = "function_code";

    public AcJsonBindConfig $acJsonBindConfig;
    public string $functionName = "";
    public string $functionCode = "";

    public static function instanceFromJson(array $jsonData): self {
        $instance = new self();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public static function getInstance(string $functionName, string $dataDictionaryName = "default"): self {
        $result = new self();
        $acDataDictionary = AcDataDictionary::getInstance($dataDictionaryName);
        if (isset($acDataDictionary->functions[$functionName])) {
            $result->fromJson($acDataDictionary->functions[$functionName]);
        }
        return $result;
    }

    public function __construct() {
        $this->acJsonBindConfig = AcJsonBindConfig::instanceFromJson(jsonData: [
            AcJsonBindConfig::KEY_PROPERY_BINDINGS => [
                self::KEY_FUNCTION_CODE => "functionCode",
                self::KEY_FUNCTION_NAME => "functionName",
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
