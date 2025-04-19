<?php
namespace AcWeb\ApiDocs\Models;

class AcApiDocExternalDocs {
    const KEY_DESCRIPTION = "description";
    const KEY_URL = "url";

    public string $description = "";
    public string $url = "";

    public static function fromJson(array $jsonData): AcApiDocExternalDocs {
        $instance = new AcApiDocExternalDocs();
        $instance->setValuesFromJson($jsonData);
        return $instance;
    }

    public function setValuesFromJson(array $jsonData) {
        if (isset($jsonData[self::KEY_DESCRIPTION])) {
            $this->description = $jsonData[self::KEY_DESCRIPTION];
        }
        if (isset($jsonData[self::KEY_URL])) {
            $this->url = $jsonData[self::KEY_URL];
        }
    }

    public function toJson(): array {
        return [
            self::KEY_DESCRIPTION => $this->description,
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
