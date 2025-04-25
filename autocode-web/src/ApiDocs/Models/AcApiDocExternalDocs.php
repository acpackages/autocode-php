<?php
namespace AcWeb\ApiDocs\Models;

use Autocode\Models\AcJsonBindConfig;

class AcApiDocExternalDocs {
    const KEY_DESCRIPTION = "description";
    const KEY_URL = "url";
    public AcJsonBindConfig $acJsonBindConfig;
    public string $description = "";
    public string $url = "";

    public static function instanceFromJson(array $jsonData): AcApiDocExternalDocs {
        $instance = new AcApiDocExternalDocs();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public function fromJson(array $jsonData) {
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
