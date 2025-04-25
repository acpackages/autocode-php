<?php
namespace AcWeb\ApiDocs\Modelss;

use Autocode\Models\AcJsonBindConfig;

class AcApiDocLicense {
    const KEY_NAME = "name";
    const KEY_URL = "url";
    public AcJsonBindConfig $acJsonBindConfig;
    public $name = "";
    public $url = "";

    public static function instanceFromJson(array $jsonData): AcApiDocLicense {
        $instance = new AcApiDocLicense();
        $instance->fromJson($jsonData);
        return $instance;
    }


    public function fromJson(array $jsonData) {
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