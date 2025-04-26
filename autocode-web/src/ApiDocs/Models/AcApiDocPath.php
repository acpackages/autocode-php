<?php
namespace AcWeb\ApiDocs\Models;

use AcWeb\ApiDocs\Models\AcApiDocRoute;
use Autocode\Utils\AcUtilsJson;

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
