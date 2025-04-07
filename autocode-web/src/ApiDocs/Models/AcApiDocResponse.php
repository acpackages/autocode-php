<?php
namespace AcWeb\ApiDocs\Model;

class AcApiDocResponse {
    const KEY_DESCRIPTION = 'description';
    const KEY_HEADERS = 'headers';
    const KEY_CONTENT = 'content';
    const KEY_LINKS = 'links';

    public string $description = '';
    public array $headers = [];
    public array $content = [];
    public array $links = [];

    public static function fromJson(array $jsonData): AcApiDocResponse {
        $instance = new AcApiDocResponse();

        $instance->description = $jsonData[self::KEY_DESCRIPTION] ?? '';

        if (isset($jsonData[self::KEY_CONTENT])) {
            foreach ($jsonData[self::KEY_CONTENT] as $mime => $contentJson) {
                $instance->content[$mime] = AcApiDocContent::fromJson($contentJson);
            }
        }
        
        if (isset($jsonData[self::KEY_HEADERS])) {
            foreach ($jsonData[self::KEY_HEADERS] as $headerName => $headerJson) {
                $instance->headers[$headerName] = AcApiDocHeader::fromJson($headerJson);
            }
        }
        
        if (isset($jsonData[self::KEY_LINKS])) {
            foreach ($jsonData[self::KEY_LINKS] as $linkName => $linkJson) {
                $instance->links[$linkName] = AcApiDocLink::fromJson($linkJson);
            }
        }

        return $instance;
    }

    public function toJson(): array {
        return [
            self::KEY_DESCRIPTION => $this->description,
            self::KEY_HEADERS => $this->headers,
            self::KEY_CONTENT => $this->content,
            self::KEY_LINKS => $this->links,
        ];
    }

    public function toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function __toString(): string {
        return $this->toString();
    }
}
?>
