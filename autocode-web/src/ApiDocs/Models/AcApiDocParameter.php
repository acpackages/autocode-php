<?php
namespace AcWeb\ApiDocs\Models;

use Autocode\Utils\AcUtilsJson;


class AcApiDocParameter {
    const KEY_DESCRIPTION = "description"; 
    const KEY_EXPLODE = "explode";   
    const KEY_IN = "in";   
    const KEY_NAME = "name";
    const KEY_REQUIRED = "required";
    const KEY_SCHEMA = "schema";
    public ?string $description = null;
    public ?string $in = null;    
    public ?string $name = null;
    public bool $required = false;
    public bool $explode = true;
    public ?array $schema = null;

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
