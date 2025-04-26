<?php
namespace AcWeb\ApiDocs\Models;

use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Utils\AcUtilsJson;


class AcApiDocSecurityScheme {
    const KEY_TYPE = 'type';
    const KEY_DESCRIPTION = 'description';
    const KEY_NAME = 'name';
    const KEY_IN = 'in';
    const KEY_SCHEME = 'scheme';
    const KEY_BEARER_FORMAT = 'bearerFormat';
    const KEY_FLOWS = 'flows';
    const KEY_OPENID_CONNECT_URL = 'openIdConnectUrl';
    public string $type = '';
    public string $description = '';
    public string $name = '';
    public string $in = '';
    public string $scheme = '';

    #[AcBindJsonProperty(key: AcApiDocSecurityScheme::KEY_BEARER_FORMAT)]
    public string $bearerFormat = '';

    public array $flows = [];

    #[AcBindJsonProperty(key: AcApiDocSecurityScheme::KEY_OPENID_CONNECT_URL)]
    public string $openIdConnectUrl = '';

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
