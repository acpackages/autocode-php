<?php
namespace AcWeb\ApiDocs\Models;

use AcWeb\ApiDocs\Models\AcApiDocSchema;

class AcApiDocComponents {
    const KEY_SCHEMAS = 'schemas';

    public array $schemas = [];

    public static function fromJson(array $jsonData): AcApiDocComponents {
        $instance = new AcApiDocComponents();
        if (isset($jsonData[self::KEY_SCHEMAS])) {
            foreach ($jsonData[self::KEY_SCHEMAS] as $name => $schema) {
                $instance->schemas[$name] = AcApiDocSchema::fromJson($schema);
            }
        }
        return $instance;
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

    public function toString():string{
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }
}
?>
