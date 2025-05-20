<?php
namespace AcWeb\ApiDocs\Models;

use AcWeb\ApiDocs\Models\AcApiDocSchema;
use Autocode\Utils\AcJsonUtils;

class AcApiDocComponents {
    const KEY_SCHEMAS = 'schemas';
    public array $schemas = [];

    public static function instanceFromJson(array $jsonData): static {
        $instance = new self();
        $instance->fromJson(jsonData: $jsonData);
        return $instance;
    }

    public function fromJson(array $jsonData = []): static {
        if (isset($jsonData[self::KEY_SCHEMAS])) {
            foreach ($jsonData[self::KEY_SCHEMAS] as $name => $schema) {
                $this->schemas[$name] = AcApiDocSchema::instanceFromJson($schema);
            }
            unset($jsonData[self::KEY_SCHEMAS]);
        }
        AcJsonUtils::setInstancePropertiesFromJsonData(instance: $this, jsonData: $jsonData);
        return $this;
    }

    public function toJson(): array {
        $result = [];    
        if (!empty($this->schemas)) {
            $schemas = [];
            foreach ($this->schemas as $name => $schema) {
                $schemaJson = $schema->toJson();
                if (!empty($schemaJson)) {
                    $schemas[$name] = $schemaJson;
                }
            }    
            if (!empty($schemas)) {
                $result[self::KEY_SCHEMAS] = $schemas;
            }
        }    
        return $result;
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

}
?>
