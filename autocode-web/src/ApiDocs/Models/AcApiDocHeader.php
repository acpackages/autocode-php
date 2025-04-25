<?php
namespace AcWeb\ApiDocs\Models;

use Autocode\Models\AcJsonBindConfig;

class AcApiDocHeader {
    const KEY_DESCRIPTION = 'description';
    const KEY_REQUIRED = 'required';
    const KEY_DEPRECATED = 'deprecated';
    const KEY_SCHEMA = 'schema';
    public AcJsonBindConfig $acJsonBindConfig;
    public string $description = '';
    public bool $required = false;
    public bool $deprecated = false;
    public array $schema = [];

    public static function instanceFromJson(array $jsonData): AcApiDocHeader {
        $instance = new AcApiDocHeader();

        $instance->description = $jsonData[self::KEY_DESCRIPTION] ?? '';
        $instance->required = $jsonData[self::KEY_REQUIRED] ?? false;
        $instance->deprecated = $jsonData[self::KEY_DEPRECATED] ?? false;
        $instance->schema = $jsonData[self::KEY_SCHEMA] ?? [];

        return $instance;
    }

    public function toJson(): array {
        return [
            self::KEY_DESCRIPTION => $this->description,
            self::KEY_REQUIRED => $this->required,
            self::KEY_DEPRECATED => $this->deprecated,
            self::KEY_SCHEMA => $this->schema,
        ];
    }

    public function toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

    public function __toString(): string {
        return $this->toString();
    }
}
?>
