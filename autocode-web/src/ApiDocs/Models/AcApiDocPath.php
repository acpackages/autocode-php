<?php
namespace AcWeb\ApiDocs\Models;

use AcWeb\ApiDocs\Models\AcApiDocRoute;
use Autocode\Models\AcJsonBindConfig;

class AcApiDocPath {
    const KEY_URL = "url";
    const KEY_CONNECT = "connect";   
    const KEY_GET = "get";   
    const KEY_PUT = "put";
    const KEY_POST = "post";
    const KEY_DELETE = "delete";   
    const KEY_OPTIONS = "options";
    const KEY_HEAD = "head";
    const KEY_PATCH = "patch";   
    const KEY_TRACE = "trace";
    public AcJsonBindConfig $acJsonBindConfig;
    public string $url = "";
    public ?AcApiDocRoute $connect = null;
    public ?AcApiDocRoute $get = null;
    public ?AcApiDocRoute $put = null;
    public ?AcApiDocRoute $post = null;
    public ?AcApiDocRoute $delete = null;
    public ?AcApiDocRoute $options = null;
    public ?AcApiDocRoute $head = null;
    public ?AcApiDocRoute $patch = null;
    public ?AcApiDocRoute $trace = null;

    public static function instanceFromJson(array $jsonData): AcApiDocPath {
        $instance = new AcApiDocPath();
        $instance->url = $jsonData[self::KEY_URL] ?? null;
        foreach ([self::KEY_GET, self::KEY_PUT, self::KEY_POST, self::KEY_DELETE, self::KEY_OPTIONS, self::KEY_HEAD, self::KEY_PATCH, self::KEY_TRACE] as $method) {
            if (isset($jsonData[$method])) {
                $instance->$method = AcApiDocPathOperation::instanceFromJson($jsonData[$method]);
            }
        }
        return $instance;
    }

    public function toJson(): array {
        $result = [
            self::KEY_URL => $this->url
        ];
        if($this->delete != null) {
            $result[self::KEY_DELETE] = $this->delete->toJson();
        }
        if($this->get != null) {
            $result[self::KEY_GET] = $this->get->toJson();
        }
        if($this->post != null) {
            $result[self::KEY_POST] = $this->post->toJson();
        }
        if($this->put != null) {
            $result[self::KEY_PUT] = $this->put->toJson();
        }
        return $result;
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

    public function toString():string{
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }
}
?>
