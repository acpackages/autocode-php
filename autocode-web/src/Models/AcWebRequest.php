<?php
namespace AcWeb\Models;
require_once __DIR__.'./../../../autocode/vendor/autoload.php';
use Autocode\Utils\AcUtilsJson;

class AcWebRequest {
    const KEY_COOKIES = 'cookies';
    const KEY_BODY = 'body';
    const KEY_FILES = 'files';
    const KEY_GET = 'get';
    const KEY_HEADERS = 'headers';
    const KEY_METHOD = 'method';
    const KEY_PATH_PAREMETERS = 'path_parameters';
    const KEY_POST = 'post';
    const KEY_SESSION = 'session';
    const KEY_URL = 'url';
    public array $body=[];  
    public array $cookies=[];      
    public array $files=[];
    public array $get=[];
    public array $headers=[];
    public string $method= "";
    public array $pathParameters=[];
    public array $post=[];    
    public array $session=[];
    public string $url= "";

    public static function instanceFromJson(array $jsonData): AcWebRequest {
        $instance = new AcWebRequest();
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