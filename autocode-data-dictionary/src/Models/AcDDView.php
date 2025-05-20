<?php

namespace AcDataDictionary\Models;

require_once 'AcDDViewColumn.php';
require_once 'AcDataDictionary.php';
use AcDataDictionary\Models\AcDataDictionary;
use AcDataDictionary\Models\AcDDViewColumn;
use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Utils\AcJsonUtils;

class AcDDView {
    public const KEY_VIEW_NAME = "view_name";
    public const KEY_VIEW_COLUMNS = "view_columns";
    public const KEY_VIEW_QUERY = "view_query";

    #[AcBindJsonProperty(key: AcDDView::KEY_VIEW_NAME)]
    public string $viewName = "";

    #[AcBindJsonProperty(key: AcDDView::KEY_VIEW_QUERY)]
    public string $viewQuery = "";

    #[AcBindJsonProperty(key: AcDDView::KEY_VIEW_COLUMNS)]
    public array $viewColumns = [];

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
        if (isset($jsonData[self::KEY_VIEW_COLUMNS]) && is_array($jsonData[self::KEY_VIEW_COLUMNS])) {
            foreach ($jsonData[self::KEY_VIEW_COLUMNS] as $columnName => $columnData) {
                $this->viewColumns[$columnName] = AcDDViewColumn::instanceFromJson($columnData);
            }
            unset($jsonData[self::KEY_VIEW_COLUMNS]);
        }
        AcJsonUtils::setInstancePropertiesFromJsonData(instance: $this, jsonData: $jsonData);
        return $this;
    }

    public function toJson(): array {
        return AcJsonUtils::getJsonDataFromInstance(instance: $this);
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

}
