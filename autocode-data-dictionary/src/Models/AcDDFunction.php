<?php

namespace AcDataDictionary\Models;
require_once 'AcDataDictionary.php';
use AcDataDictionary\Models\AcDataDictionary;
use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Utils\AcUtilsJson;


class AcDDFunction {
    public const KEY_FUNCTION_NAME = "function_name";
    public const KEY_FUNCTION_CODE = "function_code";

    #[AcBindJsonProperty(key: AcDDFunction::KEY_FUNCTION_NAME)]
    public string $functionName = "";

    #[AcBindJsonProperty(key: AcDDFunction::KEY_FUNCTION_CODE)]
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
