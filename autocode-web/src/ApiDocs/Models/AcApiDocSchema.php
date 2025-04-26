<?php
namespace AcWeb\ApiDocs\Models;

use Autocode\Utils\AcUtilsJson;


class AcApiDocSchema {
    const KEY_TYPE = 'type';
    const KEY_FORMAT = 'format';
    const KEY_TITLE = 'title';
    const KEY_DESCRIPTION = 'description';
    const KEY_PROPERTIES = 'properties';
    const KEY_REQUIRED = 'required';
    const KEY_ITEMS = 'items';
    const KEY_ENUM = 'enum';
    public ?string $type = null;
    public ?string $format = null;
    public ?string $title = null;
    public ?string $description = null;
    public ?array $properties = null;
    public ?array $required = null;
    public ?array $items = null;
    public ?array $enum = null;

    public static function instanceFromJson(array $jsonData): static {
        $instance = new self();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public function fromJson(array $jsonData): static {
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
