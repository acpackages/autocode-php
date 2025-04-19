<?php
namespace AcWeb\ApiDocs\Models;

class AcApiDocMediaType {
    const KEY_SCHEMA = 'schema';
    const KEY_EXAMPLES = 'examples';

    public ?array $schema = null;
    public ?array $examples = null;

    public static function fromJson(array $jsonData): AcApiDocMediaType {
        $instance = new AcApiDocMediaType();
        $instance->schema = $jsonData[self::KEY_SCHEMA] ?? null;
        $instance->examples = $jsonData[self::KEY_EXAMPLES] ?? null;
        return $instance;
    }

    public function toJson(): array {
        return array_filter([
            self::KEY_SCHEMA => $this->schema,
            self::KEY_EXAMPLES => $this->examples,
        ]);
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

    public function toString():string{
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }
}
?>
