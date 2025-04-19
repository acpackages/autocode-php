<?php
namespace AcWeb\Models;
require_once __DIR__.'./../../../autocode/vendor/autoload.php';
use Autocode\Models\AcJsonBindConfig;
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

    public static function fromJson(array $jsonData): AcWebRequest {
        $instance = new AcWebRequest();
        $instance->setValuesFromJson($jsonData);
        return $instance;
    }

    public function __construct() {
        $this->acJsonBindConfig = AcJsonBindConfig::fromJson(jsonData: [
            AcJsonBindConfig::KEY_PROPERY_BINDINGS => [
                self::KEY_BODY => "body",
                self::KEY_COOKIES => "cookies",
                self::KEY_FILES => "files",
                self::KEY_GET => "get",
                self::KEY_HEADERS => "headers",
                self::KEY_METHOD => "method",                
                self::KEY_PATH_PAREMETERS => "pathParameters",
                self::KEY_POST => "post",
                self::KEY_SESSION => "session",
                self::KEY_URL => "url",
            ]        
        ]);
    }

    public function setValuesFromJson(array $jsonData = []): void {
        AcUtilsJson::bindInstancePropertiesFromJson(instance: $this, data: $jsonData);
    }

    public function toJson(): array {
        return AcUtilsJson::createJsonArrayFromInstance(instance: $this);
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

    public function toString():string{
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }
}

?>