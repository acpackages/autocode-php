<?php
namespace AcWeb\ApiDocs\Models;

use Autocode\Models\AcJsonBindConfig;
class AcApiDocContact {
    const KEY_EMAIL = "email";
    const KEY_NAME = "name";
    const KEY_URL = "url";
    public AcJsonBindConfig $acJsonBindConfig;
    public string $email = "";
    public string $name = "";
    public string $url = "";
    
    public static function instanceFromJson(array $jsonData): AcApiDocContact {
        $instance = new AcApiDocContact();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public function __construct() {
        $this->acJsonBindConfig = AcJsonBindConfig::instanceFromJson(jsonData: [
            AcJsonBindConfig::KEY_PROPERY_BINDINGS => [
                self::KEY_EMAIL => "email",
                self::KEY_NAME => "name",
                self::KEY_URL => "url",
            ]        
        ]);
    }


    public function fromJson(array $jsonData) {
        if (isset($jsonData[self::KEY_EMAIL])) {
            $this->email = $jsonData[self::KEY_EMAIL];
        }
        if (isset($jsonData[self::KEY_NAME])) {
            $this->name = $jsonData[self::KEY_NAME];
        }
        if (isset($jsonData[self::KEY_URL])) {
            $this->url = $jsonData[self::KEY_URL];
        }
    }

    public function toJson(): array {
        $result = [];    
        if (!empty($this->email)) {
            $result[self::KEY_EMAIL] = $this->email;
        }
        if (!empty($this->name)) {
            $result[self::KEY_NAME] = $this->name;
        }
        if (!empty($this->url)) {
            $result[self::KEY_URL] = $this->url;
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