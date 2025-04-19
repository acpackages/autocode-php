<?php

namespace AcDataDictionary\Models;
require_once 'AcDDTableField.php';
require_once 'AcDDTableProperty.php';
require_once 'AcDataDictionary.php';
use AcDataDictionary\Models\AcDataDictionary;
use AcDataDictionary\Models\AcDDTableField;
use AcDataDictionary\Models\AcDDTableProperty;
use Autocode\Models\AcJsonBindConfig;
use Autocode\Utils\AcUtilsJson;

class AcDDSelectStatement {

    const KEY_EXCLUDE_FIELDS = "exclude_fields";
    const KEY_INCLUDE_FIELDS = "include_fields";
    const KEY_FILTERS = "filters";
    const KEY_TABLE_NAME = "table_name";

    public AcJsonBindConfig $acJsonBindConfig;
    public array $excludeFields = [];    
    public array $filters = [];
    public array $includeFields = [];
    public string $tableName = "";

    public static function fromJson(array $jsonData): AcDDSelectStatement {
        $instance = new self();
        $instance->setValuesFromJson($jsonData);
        return $instance;
    }

    public function __construct() {
        $this->acJsonBindConfig = AcJsonBindConfig::fromJson(jsonData: [
            AcJsonBindConfig::KEY_PROPERY_BINDINGS => [
                self::KEY_EXCLUDE_FIELDS => "excludeFields",
                self::KEY_FILTERS => "filters",
                self::KEY_INCLUDE_FIELDS => "includeFields",
                self::KEY_TABLE_NAME => "tableName"
            ]        
        ]);
    }

    public function setValuesFromJson(array $jsonData = []): void {
        AcUtilsJson::bindInstancePropertiesFromJson(instance: $this, data: $jsonData);
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
