<?php
namespace AcWeb\ApiDocs\Model;

class AcApiDocSchema {
    const KEY_TYPE = 'type';
    const KEY_FORMAT = 'format';
    const KEY_TITLE = 'title';
    const KEY_DESCRIPTION = 'description';
    const KEY_PROPERTIES = 'properties';
    const KEY_REQUIRED = 'required';
    const KEY_ITEMS = 'items';
    const KEY_ENUM = 'enum';

    public ?string $type = null;
    public ?string $format = null;
    public ?string $title = null;
    public ?string $description = null;
    public ?array $properties = null;
    public ?array $required = null;
    public ?array $items = null;
    public ?array $enum = null;

    public static function fromJson(array $jsonData): AcApiDocSchema {
        $instance = new AcApiDocSchema();
        $instance->type = $jsonData[self::KEY_TYPE] ?? null;
        $instance->format = $jsonData[self::KEY_FORMAT] ?? null;
        $instance->title = $jsonData[self::KEY_TITLE] ?? null;
        $instance->description = $jsonData[self::KEY_DESCRIPTION] ?? null;
        $instance->properties = $jsonData[self::KEY_PROPERTIES] ?? null;
        $instance->required = $jsonData[self::KEY_REQUIRED] ?? null;
        $instance->items = $jsonData[self::KEY_ITEMS] ?? null;
        $instance->enum = $jsonData[self::KEY_ENUM] ?? null;
        return $instance;
    }

    public function toJson(): array {
        return array_filter([
            self::KEY_TYPE => $this->type,
            self::KEY_FORMAT => $this->format,
            self::KEY_TITLE => $this->title,
            self::KEY_DESCRIPTION => $this->description,
            self::KEY_PROPERTIES => $this->properties,
            self::KEY_REQUIRED => $this->required,
            self::KEY_ITEMS => $this->items,
            self::KEY_ENUM => $this->enum,
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
