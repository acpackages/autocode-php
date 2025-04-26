<?php

namespace AcDataDictionary\Models;

require_once 'AcDDViewField.php';
require_once 'AcDataDictionary.php';
use AcDataDictionary\Models\AcDataDictionary;
use AcDataDictionary\Models\AcDDViewField;
use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Utils\AcUtilsJson;

class AcDDView {
    public const KEY_VIEW_NAME = "view_name";
    public const KEY_VIEW_FIELDS = "view_fields";
    public const KEY_VIEW_QUERY = "view_query";

    #[AcBindJsonProperty(key: AcDDView::KEY_VIEW_NAME)]
    public string $viewName = "";

    #[AcBindJsonProperty(key: AcDDView::KEY_VIEW_QUERY)]
    public string $viewQuery = "";

    #[AcBindJsonProperty(key: AcDDView::KEY_VIEW_FIELDS)]
    public array $viewFields = [];

    public static function instanceFromJson(array $jsonData): self {
        $instance = new self();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public static function getInstance(string $viewName, string $dataDictionaryName = "default"): static {
        $result = new self();
        $acDataDictionary = AcDataDictionary::getInstance($dataDictionaryName);
        if (isset($acDataDictionary->views[$viewName])) {
            $result->fromJson($acDataDictionary->views[$viewName]);
        }
        return $result;
    }

    public function fromJson(array $jsonData = []): static {
        if (isset($jsonData[self::KEY_VIEW_FIELDS]) && is_array($jsonData[self::KEY_VIEW_FIELDS])) {
            foreach ($jsonData[self::KEY_VIEW_FIELDS] as $fieldName => $fieldData) {
                $this->viewFields[$fieldName] = AcDDViewField::instanceFromJson($fieldData);
            }
            unset($jsonData[self::KEY_VIEW_FIELDS]);
        }
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
