<?php
namespace AcWeb\ApiDocs\Models;

use Autocode\Models\AcJsonBindConfig;

class AcApiDocRequestBody {
    const KEY_DESCRIPTION = 'description';
    const KEY_CONTENT = 'content';
    const KEY_REQUIRED = 'required';
    public AcJsonBindConfig $acJsonBindConfig;
    public ?string $description = "";
    public array $content = [];
    public bool $required = false;

    public static function instanceFromJson(array $jsonData): AcApiDocRequestBody {
        $instance = new AcApiDocRequestBody();
        $instance->description = $jsonData[self::KEY_DESCRIPTION] ?? null;
        if (isset($jsonData[self::KEY_CONTENT])) {
            foreach ($jsonData[self::KEY_CONTENT] as $mime => $contentJson) {
                $instance->content[$mime] = AcApiDocContent::instanceFromJson($contentJson);
            }
        }
        $instance->required = $jsonData[self::KEY_REQUIRED] ?? false;
        return $instance;
    }

    public function addContent(AcApiDocContent $content): void{
        $this->content[] = $content;
    }

    public function toJson(): array {
        $result = [];
        if($this->description != ""){
            $result[self::KEY_DESCRIPTION] = $this->description;
        }
        if($this->required){
            $result[self::KEY_REQUIRED] = $this->required;
        }
        if(sizeof($this->content) > 0){
            $result[self::KEY_CONTENT] = [];
            foreach ($this->content as $content) {
                $result[self::KEY_CONTENT][$content->encoding] = $content->toJson();
            }
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
