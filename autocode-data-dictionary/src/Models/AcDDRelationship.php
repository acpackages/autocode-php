<?php

namespace AcDataDictionary\Models;
require_once 'AcDataDictionary.php';
use AcDataDictionary\Models\AcDataDictionary;
use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Utils\AcJsonUtils;

class AcDDRelationship {
    public const KEY_CASCADE_DELETE_DESTINATION = "cascade_delete_destination";
    public const KEY_CASCADE_DELETE_SOURCE = "cascade_delete_source";
    public const KEY_DESTINATION_COLUMN = "destination_column";
    public const KEY_DESTINATION_TABLE = "destination_table";
    public const KEY_SOURCE_COLUMN = "source_column";
    public const KEY_SOURCE_TABLE = "source_table";

    #[AcBindJsonProperty(key: AcDDFunction::KEY_CASCADE_DELETE_DESTINATION)]
    public bool $cascadeDeleteDestination = false;

    #[AcBindJsonProperty(key: AcDDFunction::KEY_CASCADE_DELETE_SOURCE)]
    public bool $cascadeDeleteSource = false;

    #[AcBindJsonProperty(key: AcDDFunction::KEY_DESTINATION_COLUMN)]
    public string $destinationColumn = "";

    #[AcBindJsonProperty(key: AcDDFunction::KEY_DESTINATION_TABLE)]
    public string $destinationTable = "";

    #[AcBindJsonProperty(key: AcDDFunction::KEY_SOURCE_COLUMN)]
    public string $sourceColumn = "";

    #[AcBindJsonProperty(key: AcDDFunction::KEY_SOURCE_TABLE)]
    public string $sourceTable = "";

    public static function instanceFromJson(array $jsonData): self {
        $instance = new self();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public static function getInstances(string $destinationColumn, string $destinationTable, string $dataDictionaryName = "default"): array {
        $result = [];
        $acDataDictionary = AcDataDictionary::getInstance($dataDictionaryName);

        if (isset($acDataDictionary->relationships[$destinationTable][$destinationColumn])) {
            foreach ($acDataDictionary->relationships[$destinationTable][$destinationColumn] as $sourceTable => $sourceDetails) {
                foreach ($sourceDetails as $sourceColumn => $relationshipDetails) {
                    $result[] = self::instanceFromJson($relationshipDetails);
                }
            }
        }

        return $result;
    }

    public function fromJson(array $jsonData = []): static {
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

?>
