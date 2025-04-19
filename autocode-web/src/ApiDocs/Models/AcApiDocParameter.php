<?php
namespace AcWeb\ApiDocs\Models;

class AcApiDocParameter {
    const KEY_DESCRIPTION = "description"; 
    const KEY_EXPLODE = "explode";   
    const KEY_IN = "in";   
    const KEY_NAME = "name";
    const KEY_REQUIRED = "required";
    const KEY_SCHEMA = "schema";
    public ?string $description = null;
    public ?string $in = null;    
    public ?string $name = null;
    public bool $required = false;
    public bool $explode = true;
    public ?array $schema = null;

    public static function fromJson(array $jsonData): AcApiDocParameter {
        $instance = new AcApiDocParameter();
        $instance->name = $jsonData[self::KEY_NAME];
        $instance->in = $jsonData[self::KEY_IN];
        $instance->description = $jsonData[self::KEY_DESCRIPTION] ?? null;
        $instance->required = $jsonData[self::KEY_REQUIRED] ?? false;
        $instance->explode = $jsonData[self::KEY_REQUIRED] ?? false;
        $instance->schema = $jsonData[self::KEY_SCHEMA] ?? null;
        return $instance;
    }

    public function toJson(): array {
        return array_filter([
            self::KEY_NAME => $this->name,
            self::KEY_EXPLODE => $this->explode,
            self::KEY_IN => $this->in,
            self::KEY_DESCRIPTION => $this->description,
            self::KEY_REQUIRED => $this->required,
            self::KEY_SCHEMA => $this->schema,
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
