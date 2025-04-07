<?php
namespace AcWeb\ApiDocs\Model;
class AcApiDocContact {
    const KEY_EMAIL = "email";
    const KEY_NAME = "name";
    const KEY_URL = "url";

    
    public ?string $email = null;
    public ?string $name = null;
    public ?string $url = null;
    
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
        return [
            self::KEY_EMAIL => $this->email,
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