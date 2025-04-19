<?php
namespace AcWeb\ApiDocs\Modelss;

class AcApiDocLicense {
    const KEY_NAME = "name";
    const KEY_URL = "url";

    public $name = "";
    public $url = "";

    public static function fromJson(array $jsonData): AcApiDocLicense {
        $instance = new AcApiDocLicense();
        $instance->setValuesFromJson($jsonData);
        return $instance;
    }


    public function setValuesFromJson(array $jsonData) {
        if (isset($jsonData[self::KEY_NAME])) {
            $this->name = $jsonData[self::KEY_NAME];
        }
        if (isset($jsonData[self::KEY_URL])) {
            $this->url = $jsonData[self::KEY_URL];
        }
    }

    public function toJson(): array {
        return [
            self::KEY_NAME => $this->name,
            self::KEY_URL => $this->url,
        ];
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

    public function toString():string{
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }
    
}

?>