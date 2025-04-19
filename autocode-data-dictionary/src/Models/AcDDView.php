<?php

namespace AcDataDictionary\Models;

require_once 'AcDDViewField.php';
require_once 'AcDataDictionary.php';
use AcDataDictionary\Models\AcDataDictionary;
use AcDataDictionary\Models\AcDDViewField;
use Autocode\Models\AcJsonBindConfig;
use Autocode\Utils\AcUtilsJson;

class AcDDView {
    public const KEY_VIEW_NAME = "view_name";
    public const KEY_VIEW_FIELDS = "view_fields";
    public const KEY_VIEW_QUERY = "view_query";

    public AcJsonBindConfig $acJsonBindConfig;
    public string $viewName = "";
    public string $viewQuery = "";
    public array $viewFields = [];

    public static function fromJson(array $jsonData): AcDDView {
        $instance = new self();
        $instance->setValuesFromJson($jsonData);
        return $instance;
    }

    public static function getInstance(string $viewName, string $dataDictionaryName = "default"): AcDDView {
        $result = new self();
        $acDataDictionary = AcDataDictionary::getInstance($dataDictionaryName);
        if (isset($acDataDictionary->views[$viewName])) {
            $result->setValuesFromJson($acDataDictionary->views[$viewName]);
        }
        return $result;
    }

    public function __construct() {
        $this->acJsonBindConfig = AcJsonBindConfig::fromJson(jsonData: [
            AcJsonBindConfig::KEY_PROPERY_BINDINGS => [
                self::KEY_VIEW_NAME => "viewName",
                self::KEY_VIEW_QUERY => "viewQuery",
                self::KEY_VIEW_FIELDS => "viewFields"
            ]        
        ]);
    }

    public function setValuesFromJson(array $jsonData = []): void {
        if (isset($jsonData[self::KEY_VIEW_NAME])) {
            $this->viewName = (string) $jsonData[self::KEY_VIEW_NAME];
        }
        if (isset($jsonData[self::KEY_VIEW_QUERY])) {
            $this->viewQuery = (string) $jsonData[self::KEY_VIEW_QUERY];
        }
        if (isset($jsonData[self::KEY_VIEW_FIELDS]) && is_array($jsonData[self::KEY_VIEW_FIELDS])) {
            foreach ($jsonData[self::KEY_VIEW_FIELDS] as $fieldName => $fieldData) {
                $this->viewFields[$fieldName] = AcDDViewField::fromJson($fieldData);
            }
        }
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
