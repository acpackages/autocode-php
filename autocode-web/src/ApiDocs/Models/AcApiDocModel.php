<?php
namespace AcWeb\ApiDocs\Models;

use Autocode\Utils\AcUtilsJson;


class AcApiDocModel {
    const KEY_NAME = 'name';
    const KEY_TYPE = 'type';
    const KEY_PROPERTIES = 'properties';
    public string $name = "";
    public string $type = "object";
    public array $properties = [];

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
