<?php
namespace AcWeb\ApiDocs\Models;

use AcWeb\ApiDocs\Models\AcApiDocExternalDocs;
use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Utils\AcJsonUtils;

class AcApiDocTag {
    const KEY_NAME = "name";
    const KEY_DESCRIPTION = "description";
    const KEY_EXTERNAL_DOCS = "externalDocs";
    public string $name = "";
    public string $description = "";

    #[AcBindJsonProperty(key: AcApiDocTag::KEY_EXTERNAL_DOCS)]
    public AcApiDocExternalDocs $externalDocs ;

    public static function instanceFromJson(array $jsonData): static {
        $instance = new self();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public function __construct() {
        $this->externalDocs = new AcApiDocExternalDocs();
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
