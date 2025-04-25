<?php
namespace AcWeb\ApiDocs\Models;

use Autocode\Models\AcJsonBindConfig;

class AcApiDocModel {
    const KEY_NAME = 'name';
    const KEY_TYPE = 'type';
    const KEY_PROPERTIES = 'properties';
    public AcJsonBindConfig $acJsonBindConfig;
    public string $name = "";
    public string $type = "object";
    public array $properties = [];

    public static function instanceFromJson(array $jsonData): AcApiDocModel {
        $instance = new AcApiDocModel();

        if (isset($jsonData[self::KEY_NAME])) {
            $instance->name = $jsonData[self::KEY_NAME];
        }

        if (isset($jsonData[self::KEY_PROPERTIES])) {
            $instance->properties = $jsonData[self::KEY_PROPERTIES];
        }

        if (isset($jsonData[self::KEY_TYPE])) {
            $instance->type = $jsonData[self::KEY_TYPE];
        }

        return $instance;
    }

    public function toJson(): array {
        return [
            self::KEY_NAME => $this->name,
            self::KEY_PROPERTIES => $this->properties,
            self::KEY_TYPE => $this->type
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
