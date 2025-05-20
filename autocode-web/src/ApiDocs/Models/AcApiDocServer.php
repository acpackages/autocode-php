<?php
namespace AcWeb\ApiDocs\Models;

use Autocode\Utils\AcJsonUtils;

class AcApiDocServer {
    const KEY_DESCRIPTION = "description";
    const KEY_TITLE = "title";
    const KEY_URL = "url";
    public string $description = "";
    public string $title = "";
    public string $url = "";
    
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