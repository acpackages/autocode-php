<?php 

namespace AcDataDictionary\Models;
use Autocode\Models\AcJsonBindConfig;
use Autocode\Utils\AcUtilsJson;

class AcDDTableProperty {
    const KEY_PROPERTY_NAME = "property_name";
    const KEY_PROPERTY_VALUE = "property_value";

    public AcJsonBindConfig $acJsonBindConfig;
    public string $propertyName = "";
    public mixed $propertyValue = null;

    public static function instanceFromJson(array $jsonData): AcDDTableProperty {
        $instance = new self();
        $instance->fromJson($jsonData);
        return $instance;
    }

    public function __construct() {
        $this->acJsonBindConfig = AcJsonBindConfig::instanceFromJson(jsonData: [
            AcJsonBindConfig::KEY_PROPERY_BINDINGS => [
                self::KEY_PROPERTY_NAME => "propertyName",
                self::KEY_PROPERTY_VALUE => "propertyValue",
            ]        
        ]);
    }

    public function fromJson(array $jsonData = []): static {
        AcUtilsJson::bindInstancePropertiesFromJson(instance: $this, data: $jsonData);
        return $this;
    }

    public function toJson(): array {
        return AcUtilsJson::createJsonArrayFromInstance(instance: $this);
    }

    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

    public function toString():string{
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }
}
?>