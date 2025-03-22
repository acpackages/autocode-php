<?php

namespace AcDataDictionary\Models;

require_once __DIR__ . './../AcDataDictionary.php';

class AcDDRelationship {
    public const KEY_CASCADE_DELETE_DESTINATION = "cascade_delete_destination";
    public const KEY_CASCADE_DELETE_SOURCE = "cascade_delete_source";
    public const KEY_DESTINATION_FIELD = "destination_field";
    public const KEY_DESTINATION_TABLE = "destination_table";
    public const KEY_SOURCE_FIELD = "source_field";
    public const KEY_SOURCE_TABLE = "source_table";

    public bool $cascadeDeleteDestination = false;
    public bool $cascadeDeleteSource = false;
    public string $destinationField = "";
    public string $destinationTable = "";
    public string $sourceField = "";
    public string $sourceTable = "";

    public static function fromJson(array $jsonData): self {
        $instance = new self();
        $instance->setValuesFromJson($jsonData);
        return $instance;
    }

    public static function getInstances(string $destinationField, string $destinationTable, string $dataDictionaryName = "default"): array {
        $result = [];
        $acDataDictionary = AcDataDictionary::getInstance($dataDictionaryName);

        if (isset($acDataDictionary->relationships[$destinationTable][$destinationField])) {
            foreach ($acDataDictionary->relationships[$destinationTable][$destinationField] as $sourceTable => $sourceDetails) {
                foreach ($sourceDetails as $sourceField => $relationshipDetails) {
                    $result[] = self::fromJson($relationshipDetails);
                }
            }
        }

        return $result;
    }

    public function setValuesFromJson(array $jsonData = []): void {
        if (array_key_exists(self::KEY_CASCADE_DELETE_DESTINATION, $jsonData)) {
            $this->cascadeDeleteDestination = (bool) $jsonData[self::KEY_CASCADE_DELETE_DESTINATION];
        }
        if (array_key_exists(self::KEY_CASCADE_DELETE_SOURCE, $jsonData)) {
            $this->cascadeDeleteSource = (bool) $jsonData[self::KEY_CASCADE_DELETE_SOURCE];
        }
        if (array_key_exists(self::KEY_DESTINATION_FIELD, $jsonData)) {
            $this->destinationField = (string) $jsonData[self::KEY_DESTINATION_FIELD];
        }
        if (array_key_exists(self::KEY_DESTINATION_TABLE, $jsonData)) {
            $this->destinationTable = (string) $jsonData[self::KEY_DESTINATION_TABLE];
        }
        if (array_key_exists(self::KEY_SOURCE_FIELD, $jsonData)) {
            $this->sourceField = (string) $jsonData[self::KEY_SOURCE_FIELD];
        }
        if (array_key_exists(self::KEY_SOURCE_TABLE, $jsonData)) {
            $this->sourceTable = (string) $jsonData[self::KEY_SOURCE_TABLE];
        }
    }

    public function toJson(): array {
        return [
            self::KEY_CASCADE_DELETE_DESTINATION => $this->cascadeDeleteDestination,
            self::KEY_CASCADE_DELETE_SOURCE => $this->cascadeDeleteSource,
            self::KEY_DESTINATION_FIELD => $this->destinationField,
            self::KEY_DESTINATION_TABLE => $this->destinationTable,
            self::KEY_SOURCE_FIELD => $this->sourceField,
            self::KEY_SOURCE_TABLE => $this->sourceTable,
        ];
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_THROW_ON_ERROR);
    }
}

?>
