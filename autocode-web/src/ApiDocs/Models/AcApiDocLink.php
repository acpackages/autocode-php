<?php
namespace AcWeb\ApiDocs\Models;

use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Utils\AcUtilsJson;


class AcApiDocLink {
    const KEY_OPERATION_ID = 'operationId';
    const KEY_PARAMETERS = 'parameters';
    const KEY_DESCRIPTION = 'description';
    
    #[AcBindJsonProperty(key: AcApiDoc::KEY_OPERATION_ID)]
    public string $operationId = '';
    public array $parameters = [];
    public string $description = '';

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
