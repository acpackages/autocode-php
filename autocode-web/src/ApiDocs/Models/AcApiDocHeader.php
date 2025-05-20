<?php
namespace AcWeb\ApiDocs\Models;

use Autocode\Utils\AcJsonUtils;


class AcApiDocHeader {
    const KEY_DESCRIPTION = 'description';
    const KEY_REQUIRED = 'required';
    const KEY_DEPRECATED = 'deprecated';
    const KEY_SCHEMA = 'schema';
    public string $description = '';
    public bool $required = false;
    public bool $deprecated = false;
    public array $schema = [];

    public static function instanceFromJson(array $jsonData): static {
        $instance = new self();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public function fromJson(array $jsonData): static {
        AcJsonUtils::setInstancePropertiesFromJsonData(instance: $this, jsonData: $jsonData);
        return $this;
    }

    public function toJson(): array {
        return AcJsonUtils::getJsonDataFromInstance(instance: $this);
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }
}
?>
