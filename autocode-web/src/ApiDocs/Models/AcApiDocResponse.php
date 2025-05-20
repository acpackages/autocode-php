<?php
namespace AcWeb\ApiDocs\Models;

use Autocode\Annotaions\AcBindJsonProperty;
use Autocode\Utils\AcJsonUtils;


class AcApiDocResponse {
    const KEY_CODE = 'code';
    const KEY_DESCRIPTION = 'description';
    const KEY_HEADERS = 'headers';
    const KEY_CONTENT = 'content';
    const KEY_LINKS = 'links';
    public int $code = 0;
    public string $description = '';
    public array $headers = [];
    #[AcBindJsonProperty(key: AcApiDocRoute::KEY_RESPONSES, skipInToJson:true)]
    public array $content = [];
    public array $links = [];

    public static function instanceFromJson(array $jsonData): static {
        $instance = new self();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public function addContent(AcApiDocContent $content): void{
        $this->content[] = $content;
    }

    public function fromJson(array $jsonData): static {
        if (isset($jsonData[self::KEY_CONTENT])) {
            foreach ($jsonData[self::KEY_CONTENT] as $mime => $contentJson) {
                $this->content[$mime] = AcApiDocContent::instanceFromJson($contentJson);
            }
            unset($jsonData[self::KEY_CONTENT]);
        }
        
        if (isset($jsonData[self::KEY_HEADERS])) {
            foreach ($jsonData[self::KEY_HEADERS] as $headerName => $headerJson) {
                $this->headers[$headerName] = AcApiDocHeader::instanceFromJson($headerJson);
            }
            unset($jsonData[self::KEY_HEADERS]);
        }
        
        if (isset($jsonData[self::KEY_LINKS])) {
            foreach ($jsonData[self::KEY_LINKS] as $linkName => $linkJson) {
                $this->links[$linkName] = AcApiDocLink::instanceFromJson($linkJson);
            }
            unset($jsonData[self::KEY_LINKS]);
        }
        AcJsonUtils::setInstancePropertiesFromJsonData(instance: $this, jsonData: $jsonData);
        return $this;
    }

    public function toJson(): array {
        $result = AcJsonUtils::getJsonDataFromInstance(instance: $this);;
        if(sizeof($this->content) > 0){
            $result[self::KEY_CONTENT] = [];
            foreach ($this->content as $content) {
                $result[self::KEY_CONTENT][$content->encoding] = $content->toJson();
            }
        }
        return $result;
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
?>
