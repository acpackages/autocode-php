<?php
namespace AcWeb\Models;
require_once __DIR__.'./../../../autocode/vendor/autoload.php';
use AcWeb\ApiDocs\Models\AcApiDocRoute;
use Autocode\Utils\AcUtilsJson;

class AcWebRouteDefinition {
    const KEY_CONTROLLER = 'controller';
    const KEY_HANDLER = 'handler';
    const KEY_DOCUMENTATION = 'documentation';
    const KEY_METHOD = 'method';
    const KEY_URL = 'url';

    
    public mixed $controller = null;  
    public mixed $handler = null;      
    public AcApiDocRoute $documentation ;
    public string $method = "POST";
    public string $url = "";

    public static function instanceFromJson(array $jsonData): AcWebRouteDefinition {
        $instance = new AcWebRouteDefinition();
        $instance->fromJson($jsonData);
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