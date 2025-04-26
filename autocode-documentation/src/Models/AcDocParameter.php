<?php
namespace AcDoc\Models;

use Autocode\Utils\AcUtilsJson;

/**
 * AcDocParameter represents a parameter for a function, method, or API endpoint.
 * It includes metadata about the parameter such as its name, type, description, and constraints (e.g., required, min/max length, regex).
 * 
 * @acDoc {
 *   "name": "AcDocParameter",
 *   "type": "class",
 *   "description": "Represents a parameter for a function, method, or API endpoint, including its name, type, description, and constraints.",
 *   "properties": {
 *     "name": "The name of the parameter.",
 *     "type": "The data type of the parameter (e.g., string, int).",
 *     "description": "A description of the parameter and its usage.",
 *     "constraints": "Optional constraints on the parameter (e.g., required, min/max value, regex pattern)."
 *   }
 * }
 */
class AcDocParameter
{
    // Constants for JSON keys
    const KEY_NAME = 'name';
    const KEY_TYPE = 'type';
    const KEY_DESCRIPTION = 'description';
    const KEY_CONSTRAINTS = 'constraints';

    /**
     * @acDoc {
     *   "name": "name",
     *   "type": "property",
     *   "description": "The name of the parameter."
     * }
     */
    public string $name;

    /**
     * @acDoc {
     *   "name": "type",
     *   "type": "property",
     *   "description": "The data type of the parameter (e.g., string, int)."
     * }
     */
    public string $type;

    /**
     * @acDoc {
     *   "name": "description",
     *   "type": "property",
     *   "description": "A description of the parameter and its usage."
     * }
     */
    public ?string $description = null;

    /**
     * @acDoc {
     *   "name": "constraints",
     *   "type": "property",
     *   "description": "Optional constraints on the parameter (e.g., required, min/max value, regex pattern)."
     * }
     */
    public ?array $constraints = null;

    /**
     * Constructor method to initialize parameter properties.
     * 
     * @acDoc {
     *   "name": "constructor",
     *   "type": "method",
     *   "description": "Constructor to initialize the parameter with a name, type, description, and optional constraints."
     * }
     */
    public function __construct(string $name, string $type, ?string $description = null, ?array $constraints = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->description = $description;
        $this->constraints = $constraints ?? [];
    }

    /**
     * Static method to create an AcDocParameter instance from JSON data.
     * 
     * @acDoc {
     *   "name": "instanceFromJson",
     *   "type": "method",
     *   "description": "Creates an instance of AcDocParameter from a JSON array."
     * }
     */
    public static function instanceFromJson(array $jsonData): static {
        $instance = new self(
            $jsonData[self::KEY_NAME] ?? '',
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
     *   "description": "Sets the values of the instance properties from the JSON data."
     * }
     */
    public function fromJson(array $jsonData = []): static {
        AcUtilsJson::setInstancePropertiesFromJsonData(instance: $this, jsonData: $jsonData);
        return $this;
    }

    /**
     * Convert the AcDocParameter instance to a JSON array.
     * 
     * @acDoc {
     *   "name": "toJson",
     *   "type": "method",
     *   "description": "Converts the instance into a JSON array representation."
     * }
     */
    public function toJson(): array {
        return AcUtilsJson::getJsonDataFromInstance(instance: $this);
    }

    /**
     * Convert the AcDocParameter instance to a JSON string.
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
}
