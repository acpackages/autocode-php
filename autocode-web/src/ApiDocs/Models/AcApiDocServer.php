<?php
namespace AcWeb\ApiDocs\Model;
class AcApiDocServer {
    const KEY_DESCRIPTION = "description";
    const KEY_TITLE = "title";
    const KEY_URL = "url";

    
    public string $description = "";
    public string $title = "";
    public string $url = "";
    
    public static function fromJson(array $jsonData): AcApiDocServer {
        $instance = new AcApiDocServer();
        $instance->setValuesFromJson($jsonData);
        return $instance;
    }


    public function setValuesFromJson(array $jsonData) {
        if (isset($jsonData[self::KEY_DESCRIPTION])) {
            $this->description = $jsonData[self::KEY_DESCRIPTION];
        }
        if (isset($jsonData[self::KEY_TITLE])) {
            $this->title = $jsonData[self::KEY_TITLE];
        }
        if (isset($jsonData[self::KEY_URL])) {
            $this->url = $jsonData[self::KEY_URL];
        }
    }

    public function toJson(): array {
        return [
            self::KEY_DESCRIPTION => $this->description,
            self::KEY_TITLE => $this->title,
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