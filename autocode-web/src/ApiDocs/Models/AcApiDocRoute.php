<?php
namespace AcWeb\ApiDocs\Models;

use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Utils\AcUtilsJson;


class AcApiDocRoute {
    const KEY_TAGS = 'tags';
    const KEY_SUMMARY = 'summary';
    const KEY_DESCRIPTION = 'description';
    const KEY_OPERATION_ID = 'operationId';
    const KEY_PARAMETERS = 'parameters';
    const KEY_REQUEST_BODY = 'requestBody';
    const KEY_RESPONSES = 'responses';
    const KEY_CONSUMES = 'consumes';
    const KEY_PRODUCES = 'produces';
    const KEY_DEPRECATED = 'deprecated';
    const KEY_SECURITY = 'security';
    public array $tags = [];
    public string $summary = '';
    public string $description = '';

    #[AcBindJsonProperty(key: AcApiDocRoute::KEY_OPERATION_ID)]
    public string $operationId = '';
    public array $parameters = [];

    #[AcBindJsonProperty(key: AcApiDocRoute::KEY_REQUEST_BODY)]
    public ?AcApiDocRequestBody $requestBody = null;
    public array $responses = [];
    public array $consumes = [];
    public array $produces = [];
    public bool $deprecated = false;
    public array $security = [];

    public static function instanceFromJson(array $jsonData): static {
        $instance = new self();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public function fromJson(array $jsonData): static {
        AcUtilsJson::setInstancePropertiesFromJsonData(instance: $this, jsonData: $jsonData);
        return $this;
    }

    public function addParameter(AcApiDocParameter $parameter): static{
        $this->parameters[] = $parameter;
        return $this;
    }

    public function addTag(string $tag): static{
        $this->tags[] = $tag;
        return $this;
    }

    public function toJson(): array {
        return AcUtilsJson::getJsonDataFromInstance(instance: $this);
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

}
?>
