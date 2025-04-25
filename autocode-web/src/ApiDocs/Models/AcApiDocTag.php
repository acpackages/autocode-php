<?php
namespace AcWeb\ApiDocs\Models;

use AcWeb\ApiDocs\Models\AcApiDocExternalDocs;
use Autocode\Models\AcJsonBindConfig;

class AcApiDocTag {
    const KEY_NAME = "name";
    const KEY_DESCRIPTION = "description";
    const KEY_EXTERNAL_DOCS = "externalDocs";
    public AcJsonBindConfig $acJsonBindConfig;
    public string $name = "";
    public string $description = "";
    public AcApiDocExternalDocs $externalDocs ;

    public function __construct() {
        $this->externalDocs = new AcApiDocExternalDocs();
    }

    public static function instanceFromJson(array $jsonData): AcApiDocTag {
        $instance = new AcApiDocTag();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public function fromJson(array $jsonData) {
        if (isset($jsonData[self::KEY_NAME])) {
            $this->name = $jsonData[self::KEY_NAME];
        }
        if (isset($jsonData[self::KEY_DESCRIPTION])) {
            $this->description = $jsonData[self::KEY_DESCRIPTION];
        }
        if (isset($jsonData[self::KEY_EXTERNAL_DOCS])) {
            $this->externalDocs = AcApiDocExternalDocs::instanceFromJson($jsonData[self::KEY_EXTERNAL_DOCS]);
        }
    }

    public function toJson(): array {
        return [
            self::KEY_NAME => $this->name,
            self::KEY_DESCRIPTION => $this->description,
            self::KEY_EXTERNAL_DOCS => $this->externalDocs->toJson(),
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
