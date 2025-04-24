<?php
namespace AcDoc\Models;

use Autocode\Utils\AcUtilsJson;
use Autocode\Models\AcJsonBindConfig;

/**
 * AcDocException represents an exception used in the AcDoc framework.
 * This exception could be used for handling errors related to documentation generation, validation, or other issues within the framework.
 * 
 * @acDoc {
 *   "name": "AcDocException",
 *   "type": "class",
 *   "description": "Represents an exception used in the AcDoc framework for handling specific errors related to documentation.",
 *   "properties": {
 *     "message": "The error message describing the exception.",
 *     "code": "An optional error code associated with the exception.",
 *     "type": "The type of the exception (e.g., 'Validation', 'Runtime').",
 *     "description": "A description of the exception and its cause."
 *   }
 * }
 */
class AcDocException
{
    // Constants for JSON keys
    const KEY_TYPE = 'type';
    const KEY_DESCRIPTION = 'description';

    /**
     * @acDoc {
     *   "name": "acJsonBindConfig",
     *   "type": "property",
     *   "description": "Configuration object for binding properties from JSON."
     * }
     */
    public AcJsonBindConfig $acJsonBindConfig;

    /**
     * @acDoc {
     *   "name": "type",
     *   "type": "property",
     *   "description": "The type of the exception (e.g., 'Validation', 'Runtime')."
     * }
     */
    public string $type;

    /**
     * @acDoc {
     *   "name": "description",
     *   "type": "property",
     *   "description": "A description of the exception and its cause."
     * }
     */
    public ?string $description = null;

    /**
     * Constructor method to initialize the exception with a message, an optional code, and type/description.
     * 
     * @acDoc {
     *   "name": "constructor",
     *   "type": "method",
     *   "description": "Constructor to initialize the exception with a message, code, type, and description."
     * }
     */
    public function __construct()
    {
        $this->acJsonBindConfig = AcJsonBindConfig::fromJson(jsonData: [
            AcJsonBindConfig::KEY_PROPERY_BINDINGS => [
                self::KEY_TYPE => 'type',
                self::KEY_DESCRIPTION => 'description',
            ]        
        ]);
    }

    /**
     * Static method to create an AcDocException instance from JSON data.
     * 
     * @acDoc {
     *   "name": "fromJson",
     *   "type": "method",
     *   "description": "Creates an instance of AcDocException from a JSON array."
     * }
     */
    public static function fromJson(array $jsonData): AcDocException
    {
        $instance = new self();
        $instance->setValuesFromJson($jsonData);
        return $instance;
    }

    /**
     * Bind the instance properties from the provided JSON data.
     *
     * @acDoc {
     *   "name": "setValuesFromJson",
     *   "type": "method",
     *   "description": "Sets the values of the instance properties from the JSON data."
     * }
     */
    public function setValuesFromJson(array $jsonData = []): void {
        AcUtilsJson::bindInstancePropertiesFromJson(instance: $this, data: $jsonData);
    }

    /**
     * Convert the AcDocException instance to a JSON array.
     * 
     * @acDoc {
     *   "name": "toJson",
     *   "type": "method",
     *   "description": "Converts the instance into a JSON array representation."
     * }
     */
    public function toJson(): array
    {
        return AcUtilsJson::createJsonArrayFromInstance(instance: $this);
    }

    /**
     * Convert the AcDocException instance to a JSON string.
     * 
     * @acDoc {
     *   "name": "__toString",
     *   "type": "method",
     *   "description": "Converts the instance to a JSON string for easy display."
     * }
     */
    public function __toString(): string
    {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

    /**
     * Return a string representation of the exception (JSON format).
     *
     * @acDoc {
     *   "name": "toString",
     *   "type": "method",
     *   "description": "Returns a JSON string representation of the exception."
     * }
     */
    public function toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }
}
