<?php
namespace AcWeb\ApiDocs\Models;

use Autocode\Utils\AcUtilsJson;


class AcApiDocContent {
    const KEY_SCHEMA = 'schema';
    const KEY_EXAMPLES = 'examples';
    const KEY_ENCODING = 'encoding';
    public array $schema = [];
    public array $examples = [];
    public string $encoding = "";

    public static function instanceFromJson(array $jsonData): static {
        $instance = new self();
        $instance->fromJson(jsonData: $jsonData);
        return $instance;
    }

    public function fromJson(array $jsonData = []): static {
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
