<?php
namespace AcDoc\Models;

use Autocode\Utils\AcUtilsJson;

/**
 * AcDocReturnType represents the return type of a function, method, or API endpoint.
 * It includes metadata about the return type such as its type, description, and constraints (e.g., nullable, default value, format).
 * 
 * @acDoc {
 *   "name": "AcDocReturnType",
 *   "type": "class",
 *   "description": "Represents the return type of a function, method, or API endpoint, including its type, description, and constraints.",
 *   "properties": {
 *     "type": "The data type of the return value (e.g., string, int, array, object).",
 *     "description": "A description of the return value and its expected usage.",
 *     "constraints": "Optional constraints on the return type (e.g., nullable, default value, format)."
 *   }
 * }
 */
class AcDocReturnType
{
    // Constants for JSON keys
    const KEY_TYPE = 'type';
    const KEY_DESCRIPTION = 'description';
    const KEY_CONSTRAINTS = 'constraints';

    /**
     * @acDoc {
     *   "name": "type",
     *   "type": "property",
     *   "description": "The data type of the return value (e.g., string, int, array, object)."
     * }
     */
    public string $type;

    /**
     * @acDoc {
     *   "name": "description",
     *   "type": "property",
     *   "description": "A description of the return value and its expected usage."
     * }
     */
    public ?string $description = null;

    /**
     * @acDoc {
     *   "name": "constraints",
     *   "type": "property",
     *   "description": "Optional constraints on the return type (e.g., nullable, default value, format)."
     * }
     */
    public ?array $constraints = null;

    /**
     * Constructor method to initialize return type properties.
     * 
     * @acDoc {
     *   "name": "constructor",
     *   "type": "method",
     *   "description": "Constructor to initialize the return type with a type, description, and optional constraints."
     * }
     * @param string $type The return type of the function or method.
     * @param string|null $description A description of the return value.
     * @param array|null $constraints Constraints on the return type (nullable, default value, format, etc.).
     */
    public function __construct(string $type, ?string $description = null, ?array $constraints = null)
    {
        $this->type = $type;
        $this->description = $description;
        $this->constraints = $constraints ?? [];
    }

    /**
     * Static method to create an AcDocReturnType instance from JSON data.
     * 
     * @acDoc {
     *   "name": "instanceFromJson",
     *   "type": "method",
     *   "description": "Creates an instance of AcDocReturnType from a JSON array.",
     *   "parameters": {
     *     "jsonData": "An array containing the return type data (e.g., type, description, constraints)."
     *   },
     *   "returns": "An instance of AcDocReturnType."
     * }
     * @param array $jsonData The JSON data to create the instance.
     * @return AcDocReturnType The created AcDocReturnType instance.
     */
    public static function instanceFromJson(array $jsonData): self {
        $instance = new self(
            $jsonData[self::KEY_TYPE] ?? '',
            $jsonData[self::KEY_DESCRIPTION] ?? null,
            $jsonData[self::KEY_CONSTRAINTS] ?? []
        );
        return $instance;
    }

    /**
     * Bind the instance properties from the provided JSON data.
     *
     * @acDoc {
     *   "name": "fromJson",
     *   "type": "method",
     *   "description": "Sets the values of the instance properties from the JSON data.",
     *   "parameters": {
     *     "jsonData": "An array containing the return type data to bind to the instance."
     *   },
     *   "returns": "void"
     * }
     * @param array $jsonData The JSON data to bind to the instance.
     */
    public function fromJson(array $jsonData = []): static {
        AcUtilsJson::setInstancePropertiesFromJsonData(instance: $this, jsonData: $jsonData);
        return $this;
    }

    /**
     * Convert the AcDocReturnType instance to a JSON array.
     * 
     * @acDoc {
     *   "name": "toJson",
     *   "type": "method",
     *   "description": "Converts the instance into a JSON array representation.",
     *   "returns": "An array representing the return type instance."
     * }
     * @return array The JSON array representation of the instance.
     */
    public function toJson(): array {
        return AcUtilsJson::getJsonDataFromInstance(instance: $this);
    }

    /**
     * Convert the AcDocReturnType instance to a JSON string.
     * 
     * @acDoc {
     *   "name": "__toString",
     *   "type": "method",
     *   "description": "Converts the instance to a JSON string for easy display.",
     *   "returns": "A JSON string representation of the instance."
     * }
     * @return string The JSON string representation of the instance.
     */
    public function __toString(): string
    {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

}
