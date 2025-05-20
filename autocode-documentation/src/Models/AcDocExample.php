<?php
namespace AcDoc\Models;

use Autocode\Utils\AcJsonUtils;

/**
 * AcDocExample represents an example of code, typically used for demonstrating how a code element should be used.
 * 
 * @acDoc {
 *   "name": "AcDocExample",
 *   "type": "class",
 *   "description": "Represents an example of code, typically for documentation purposes, demonstrating usage of code elements.",
 *   "properties": {
 *     "code": "The code snippet or example being demonstrated.",
 *     "description": "A description of the code example and its purpose."
 *   }
 * }
 */
class AcDocExample
{
    // Constants for JSON keys
    const KEY_CODE = 'code';
    const KEY_DESCRIPTION = 'description';


    /**
     * @acDoc {
     *   "name": "code",
     *   "type": "property",
     *   "description": "The code snippet or example being demonstrated."
     * }
     */
    public string $code;

    /**
     * @acDoc {
     *   "name": "description",
     *   "type": "property",
     *   "description": "A description of the code example and its purpose."
     * }
     */
    public ?string $description = null;

    /**
     * Constructor method to initialize properties.
     * 
     * @acDoc {
     *   "name": "constructor",
     *   "type": "method",
     *   "description": "Constructor to initialize the code example with a code and an optional description."
     * }
     */

    /**
     * Static method to create an instance from JSON data.
     * 
     * @acDoc {
     *   "name": "instanceFromJson",
     *   "type": "method",
     *   "description": "Creates an instance of AcDocExample from a JSON array."
     * }
     */
    public static function instanceFromJson(array $jsonData): static
    {
        $instance = new self();
        $instance->fromJson($jsonData);
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
        AcJsonUtils::setInstancePropertiesFromJsonData(instance: $this, jsonData: $jsonData);
        return $this;
    }

    /**
     * Convert the AcDocExample instance to a JSON array.
     * 
     * @acDoc {
     *   "name": "toJson",
     *   "type": "method",
     *   "description": "Converts the instance into a JSON array representation."
     * }
     */
    public function toJson(): array {
        return AcJsonUtils::getJsonDataFromInstance(instance: $this);
    }

    /**
     * Convert the AcDocExample instance to a JSON string.
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
