<?php
namespace AcWeb\ApiDocs\Models;

use Autocode\Utils\AcUtilsJson;


class AcApiDocRequestBody {
    const KEY_DESCRIPTION = 'description';
    const KEY_CONTENT = 'content';
    const KEY_REQUIRED = 'required';
    public ?string $description = "";
    public array $content = [];
    public bool $required = false;

    public static function instanceFromJson(array $jsonData): static {
        $instance = new self();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public function fromJson(array $jsonData): static {
        if (isset($jsonData[self::KEY_CONTENT])) {
            foreach ($jsonData[self::KEY_CONTENT] as $mime => $contentJson) {
                $this->content[$mime] = AcApiDocContent::instanceFromJson($contentJson);
            }
            unset($jsonData[self::KEY_CONTENT]);
        }
        AcUtilsJson::setInstancePropertiesFromJsonData(instance: $this, jsonData: $jsonData);
        return $this;
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

}
?>
