<?php
namespace AcWeb\ApiDocs\Models;

use Autocode\Utils\AcUtilsJson;


class AcApiDocResponse {
    const KEY_DESCRIPTION = 'description';
    const KEY_HEADERS = 'headers';
    const KEY_CONTENT = 'content';
    const KEY_LINKS = 'links';
    public string $description = '';
    public array $headers = [];
    public array $content = [];
    public array $links = [];

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
        AcUtilsJson::setInstancePropertiesFromJsonData(instance: $this, jsonData: $jsonData);
        return $this;
    }

    public function toJson(): array {
        return AcUtilsJson::getJsonDataFromInstance(instance: $this);
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
?>
