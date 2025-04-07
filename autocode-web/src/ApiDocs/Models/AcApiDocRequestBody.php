<?php
namespace AcWeb\ApiDocs\Model;

class AcApiDocRequestBody {
    const KEY_DESCRIPTION = 'description';
    const KEY_CONTENT = 'content';
    const KEY_REQUIRED = 'required';

    public ?string $description = null;
    public ?array $content = null;
    public bool $required = false;

    public static function fromJson(array $jsonData): AcApiDocRequestBody {
        $instance = new AcApiDocRequestBody();
        $instance->description = $jsonData[self::KEY_DESCRIPTION] ?? null;
        if (isset($jsonData[self::KEY_CONTENT])) {
            foreach ($jsonData[self::KEY_CONTENT] as $mime => $contentJson) {
                $instance->content[$mime] = AcApiDocContent::fromJson($contentJson);
            }
        }
        $instance->required = $jsonData[self::KEY_REQUIRED] ?? false;
        return $instance;
    }

    public function toJson(): array {
        return array_filter([
            self::KEY_DESCRIPTION => $this->description,
            self::KEY_CONTENT => $this->content,
            self::KEY_REQUIRED => $this->required,
        ]);
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

    public function toString():string{
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }
}
?>
