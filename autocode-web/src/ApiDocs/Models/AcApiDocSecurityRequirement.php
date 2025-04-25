<?php
namespace AcWeb\ApiDocs\Models;

use Autocode\Models\AcJsonBindConfig;

class AcApiDocSecurityRequirement {
    const KEY_REQUIREMENTS = 'requirements';
    public AcJsonBindConfig $acJsonBindConfig;
    public array $requirements = [];

    public static function instanceFromJson(array $jsonData): AcApiDocSecurityRequirement {
        $instance = new AcApiDocSecurityRequirement();
        $instance->requirements = $jsonData;
        return $instance;
    }

    public function toJson(): array {
        return $this->requirements;
    }

    public function toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

    public function __toString(): string {
        return $this->toString();
    }
}
?>
