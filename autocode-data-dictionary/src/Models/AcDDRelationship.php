<?php

namespace AcDataDictionary\Models;
require_once 'AcDataDictionary.php';
use AcDataDictionary\Models\AcDataDictionary;
use Autocode\Models\AcJsonBindConfig;
use Autocode\Utils\AcUtilsJson;

class AcDDRelationship {
    public const KEY_CASCADE_DELETE_DESTINATION = "cascade_delete_destination";
    public const KEY_CASCADE_DELETE_SOURCE = "cascade_delete_source";
    public const KEY_DESTINATION_FIELD = "destination_field";
    public const KEY_DESTINATION_TABLE = "destination_table";
    public const KEY_SOURCE_FIELD = "source_field";
    public const KEY_SOURCE_TABLE = "source_table";

    public AcJsonBindConfig $acJsonBindConfig;
    public bool $cascadeDeleteDestination = false;
    public bool $cascadeDeleteSource = false;
    public string $destinationField = "";
    public string $destinationTable = "";
    public string $sourceField = "";
    public string $sourceTable = "";

    public static function instanceFromJson(array $jsonData): self {
        $instance = new self();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public static function getInstances(string $destinationField, string $destinationTable, string $dataDictionaryName = "default"): array {
        $result = [];
        $acDataDictionary = AcDataDictionary::getInstance($dataDictionaryName);

        if (isset($acDataDictionary->relationships[$destinationTable][$destinationField])) {
            foreach ($acDataDictionary->relationships[$destinationTable][$destinationField] as $sourceTable => $sourceDetails) {
                foreach ($sourceDetails as $sourceField => $relationshipDetails) {
                    $result[] = self::instanceFromJson($relationshipDetails);
                }
            }
        }

        return $result;
    }

    public function __construct() {
        $this->acJsonBindConfig = AcJsonBindConfig::instanceFromJson(jsonData: [
            AcJsonBindConfig::KEY_PROPERY_BINDINGS => [
                self::KEY_CASCADE_DELETE_DESTINATION => "cascadeDeleteDestination",
                self::KEY_CASCADE_DELETE_SOURCE => "cascadeDeleteSource",
                self::KEY_DESTINATION_FIELD => "destinationField",
                self::KEY_DESTINATION_TABLE => "destinationTable",
                self::KEY_SOURCE_FIELD => "sourceField",
                self::KEY_SOURCE_TABLE => "sourceTable",
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
