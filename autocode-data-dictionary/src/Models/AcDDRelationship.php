<?php

namespace AcDataDictionary\Models;
require_once 'AcDataDictionary.php';
use AcDataDictionary\Models\AcDataDictionary;
use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Utils\AcUtilsJson;

class AcDDRelationship {
    public const KEY_CASCADE_DELETE_DESTINATION = "cascade_delete_destination";
    public const KEY_CASCADE_DELETE_SOURCE = "cascade_delete_source";
    public const KEY_DESTINATION_FIELD = "destination_field";
    public const KEY_DESTINATION_TABLE = "destination_table";
    public const KEY_SOURCE_FIELD = "source_field";
    public const KEY_SOURCE_TABLE = "source_table";

    #[AcBindJsonProperty(key: AcDDFunction::KEY_CASCADE_DELETE_DESTINATION)]
    public bool $cascadeDeleteDestination = false;

    #[AcBindJsonProperty(key: AcDDFunction::KEY_CASCADE_DELETE_SOURCE)]
    public bool $cascadeDeleteSource = false;

    #[AcBindJsonProperty(key: AcDDFunction::KEY_DESTINATION_FIELD)]
    public string $destinationField = "";

    #[AcBindJsonProperty(key: AcDDFunction::KEY_DESTINATION_TABLE)]
    public string $destinationTable = "";

    #[AcBindJsonProperty(key: AcDDFunction::KEY_SOURCE_FIELD)]
    public string $sourceField = "";

    #[AcBindJsonProperty(key: AcDDFunction::KEY_SOURCE_TABLE)]
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
