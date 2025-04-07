<?php
namespace AcWeb\ApiDocs\Model;

use AcWeb\ApiDocs\Model\AcApiDocOperation;

class AcApiDocPath {
    const KEY_URL = "url";
    const KEY_GET = "get";   
    const KEY_PUT = "put";
    const KEY_POST = "post";
    const KEY_DELETE = "delete";   
    const KEY_OPTIONS = "options";
    const KEY_HEAD = "head";
    const KEY_PATCH = "patch";   
    const KEY_TRACE = "trace";
    
    public string $url = "";
    public ?AcApiDocOperation $get = null;
    public ?AcApiDocOperation $put = null;
    public ?AcApiDocOperation $post = null;
    public ?AcApiDocOperation $delete = null;
    public ?AcApiDocOperation $options = null;
    public ?AcApiDocOperation $head = null;
    public ?AcApiDocOperation $patch = null;
    public ?AcApiDocOperation $trace = null;

    public static function fromJson(array $jsonData): AcApiDocPath {
        $instance = new AcApiDocPath();
        $instance->url = $jsonData[self::KEY_URL] ?? null;
        foreach ([self::KEY_GET, self::KEY_PUT, self::KEY_POST, self::KEY_DELETE, self::KEY_OPTIONS, self::KEY_HEAD, self::KEY_PATCH, self::KEY_TRACE] as $method) {
            if (isset($jsonData[$method])) {
                $instance->$method = AcApiDocOperation::fromJson($jsonData[$method]);
            }
        }
        return $instance;
    }

    public function toJson(): array {
        $json = array_filter([
            self::KEY_URL => $this->url,
        ]);
        foreach ([self::KEY_GET, self::KEY_PUT, self::KEY_POST, self::KEY_DELETE, self::KEY_OPTIONS, self::KEY_HEAD, self::KEY_PATCH, self::KEY_TRACE] as $method) {
            if ($this->$method !== null) {
                $json[$method] = $this->$method->toJson();
            }
        }
        return $json;
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

    public function toString():string{
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }
}
?>
