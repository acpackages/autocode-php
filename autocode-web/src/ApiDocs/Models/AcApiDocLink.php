<?php
namespace AcWeb\ApiDocs\Models;

use Autocode\Models\AcJsonBindConfig;

class AcApiDocLink {
    const KEY_OPERATION_ID = 'operationId';
    const KEY_PARAMETERS = 'parameters';
    const KEY_DESCRIPTION = 'description';
    public AcJsonBindConfig $acJsonBindConfig;
    public string $operationId = '';
    public array $parameters = [];
    public string $description = '';

    public static function instanceFromJson(array $jsonData): AcApiDocLink {
        $instance = new AcApiDocLink();

        $instance->operationId = $jsonData[self::KEY_OPERATION_ID] ?? '';
        $instance->parameters = $jsonData[self::KEY_PARAMETERS] ?? [];
        $instance->description = $jsonData[self::KEY_DESCRIPTION] ?? '';

        return $instance;
    }

    public function toJson(): array {
        return [
            self::KEY_OPERATION_ID => $this->operationId,
            self::KEY_PARAMETERS => $this->parameters,
            self::KEY_DESCRIPTION => $this->description,
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
