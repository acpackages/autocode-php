<?php
namespace AcWeb\ApiDocs\Models;

use Autocode\Models\AcJsonBindConfig;
class AcApiDocServer {
    const KEY_DESCRIPTION = "description";
    const KEY_TITLE = "title";
    const KEY_URL = "url";
    public AcJsonBindConfig $acJsonBindConfig;
    public string $description = "";
    public string $title = "";
    public string $url = "";
    
    public static function instanceFromJson(array $jsonData): AcApiDocServer {
        $instance = new AcApiDocServer();
        $instance->fromJson($jsonData);
        return $instance;
    }


    public function fromJson(array $jsonData) {
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