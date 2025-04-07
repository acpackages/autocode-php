<?php
namespace AcWeb\ApiDocs\Model;

use AcWeb\ApiDocs\Model\AcApiDocExternalDocs;

class AcApiDocTag {
    const KEY_NAME = "name";
    const KEY_DESCRIPTION = "description";
    const KEY_EXTERNAL_DOCS = "externalDocs";

    public string $name = "";
    public string $description = "";
    public AcApiDocExternalDocs $externalDocs = new AcApiDocExternalDocs();

    public static function fromJson(array $jsonData): AcApiDocTag {
        $instance = new AcApiDocTag();
        $instance->setValuesFromJson($jsonData);
        return $instance;
    }

    public function setValuesFromJson(array $jsonData) {
        if (isset($jsonData[self::KEY_NAME])) {
            $this->name = $jsonData[self::KEY_NAME];
        }
        if (isset($jsonData[self::KEY_DESCRIPTION])) {
            $this->description = $jsonData[self::KEY_DESCRIPTION];
        }
        if (isset($jsonData[self::KEY_EXTERNAL_DOCS])) {
            $this->externalDocs = AcApiDocExternalDocs::fromJson($jsonData[self::KEY_EXTERNAL_DOCS]);
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
