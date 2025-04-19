<?php
namespace AcWeb\ApiDocs\Models;
class AcApiDocContact {
    const KEY_EMAIL = "email";
    const KEY_NAME = "name";
    const KEY_URL = "url";

    
    public string $email = "";
    public string $name = "";
    public string $url = "";
    
    public static function fromJson(array $jsonData): AcApiDocContact {
        $instance = new AcApiDocContact();
        $instance->setValuesFromJson($jsonData);
        return $instance;
    }


    public function setValuesFromJson(array $jsonData) {
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