<?php
namespace AcWeb\ApiDocs\Models;

use Autocode\Models\AcJsonBindConfig;

class AcApiDocContent {
    const KEY_SCHEMA = 'schema';
    const KEY_EXAMPLES = 'examples';
    const KEY_ENCODING = 'encoding';
    public AcJsonBindConfig $acJsonBindConfig;
    public array $schema = [];
    public array $examples = [];
    public string $encoding = "";

    public static function instanceFromJson(array $jsonData): AcApiDocContent {
        $instance = new AcApiDocContent();

        if (isset($jsonData[self::KEY_SCHEMA])) {
            $instance->schema = $jsonData[self::KEY_SCHEMA];
        }

        if (isset($jsonData[self::KEY_EXAMPLES])) {
            $instance->examples = $jsonData[self::KEY_EXAMPLES];
        }

        if (isset($jsonData[self::KEY_ENCODING])) {
            $instance->encoding = $jsonData[self::KEY_ENCODING];
        }

        return $instance;
    }

    public function __construct() {
        $this->acJsonBindConfig = AcJsonBindConfig::instanceFromJson(jsonData: [
            AcJsonBindConfig::KEY_PROPERY_BINDINGS => [
                self::KEY_SCHEMA => "schema",
                self::KEY_EXAMPLES => "name",
            ]        
        ]);
    }

    public function toJson(): array {
        return [
            self::KEY_SCHEMA => $this->schema,
            self::KEY_EXAMPLES => $this->examples,
            self::KEY_ENCODING => $this->encoding,
        ];
    }

    public function toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

    public function __toString(): string {
        return $this->toString();
    }
}
?>
