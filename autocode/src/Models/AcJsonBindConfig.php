<?php

namespace Autocode\Models;

class AcJsonBindConfig {
    const KEY_PROPERY_BINDINGS = "property_bindings";
    public array $propertyBindings = [];
    public static function fromJson(array $jsonData): AcJsonBindConfig {
        $instance = new AcJsonBindConfig();
        $instance->setValuesFromJson($jsonData);
        return $instance;
    }
    
    public function setValuesFromJson(array $jsonData) {
        if (isset($jsonData[self::KEY_PROPERY_BINDINGS])) {
            $this->propertyBindings = $jsonData[self::KEY_PROPERY_BINDINGS];
        }
    }

    public function toJson(): array {
        return [
            self::KEY_PROPERY_BINDINGS => $this->propertyBindings
        ];
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

    public function toString():string{
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }
}
