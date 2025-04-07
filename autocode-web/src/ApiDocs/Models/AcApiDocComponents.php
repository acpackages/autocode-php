<?php
namespace AcWeb\ApiDocs\Model;

use AcWeb\ApiDocs\Model\AcApiDocSchema;

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
        $schemas = [];
        foreach ($this->schemas as $name => $schema) {
            $schemas[$name] = $schema->toJson();
        }
        return [
            self::KEY_SCHEMAS => $schemas,
        ];
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

    public function toString():string{
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }
}
?>
