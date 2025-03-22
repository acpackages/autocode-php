<?php

namespace AcDataDictionary\Models;

require_once __DIR__ . './../AcDataDictionary.php';

class AcDDFunction {
    public const KEY_FUNCTION_NAME = "function_name";
    public const KEY_FUNCTION_CODE = "function_code";

    public string $functionName = "";
    public string $functionCode = "";

    public static function fromJson(array $jsonData): self {
        $instance = new self();
        $instance->setValuesFromJson($jsonData);
        return $instance;
    }

    public static function getInstance(string $functionName, string $dataDictionaryName = "default"): self {
        $result = new self();
        $acDataDictionary = AcDataDictionary::getInstance($dataDictionaryName);

        if (isset($acDataDictionary->functions[$functionName])) {
            $result->setValuesFromJson($acDataDictionary->functions[$functionName]);
        }

        return $result;
    }

    public function setValuesFromJson(array $jsonData = []): void {
        if (array_key_exists(self::KEY_FUNCTION_NAME, $jsonData)) {
            $this->functionName = (string) $jsonData[self::KEY_FUNCTION_NAME];
        }
        if (array_key_exists(self::KEY_FUNCTION_CODE, $jsonData)) {
            $this->functionCode = (string) $jsonData[self::KEY_FUNCTION_CODE];
        }
    }

    public function toJson(): array {
        return [
            self::KEY_FUNCTION_NAME => $this->functionName,
            self::KEY_FUNCTION_CODE => $this->functionCode,
        ];
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_THROW_ON_ERROR);
    }
}

?>
