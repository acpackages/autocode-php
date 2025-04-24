<?php

namespace AcDoc\Models;

require_once __DIR__.'./../../../autocode/vendor/autoload.php';

use Autocode\Models\AcJsonBindConfig;
use Autocode\Utils\AcUtilsJson;

/**
 * AcDocumentation model to represent documentation for functions, classes, or methods.
 * 
 * @acDoc {
 *   "name": "AcDocumentation",
 *   "type": "class",
 *   "description": "Represents the documentation for various code elements like functions, methods, or classes.",
 *   "properties": {
 *     "name": "The name of the documented code element.",
 *     "type": "The type of the code element (e.g., 'class', 'method').",
 *     "namespace": "The namespace of the code element, if available.",
 *     "description": "A brief description of what the code element does.",
 *     "details": "Any additional details about the code element.",
 *     "category": "The category of the documentation (e.g., 'API', 'Utility').",
 *     "visibility": "The visibility of the code element (e.g., 'public', 'private').",
 *     "deprecated": "Indicates whether the code element is deprecated.",
 *     "since": "The version of the code element.",
 *     "parameters": "List of parameters for methods or functions.",
 *     "return": "Return type and description for the code element.",
 *     "throws": "List of exceptions the code element can throw.",
 *     "examples": "Examples demonstrating the usage of the code element.",
 *     "author": "The author of the code element.",
 *     "maintainer": "The maintainer of the code element.",
 *     "lastModified": "The date when the code element was last modified.",
 *     "file": "The file in which the code element is located.",
 *     "tags": "Additional tags associated with the code element.",
 *     "related": "Related code elements or references.",
 *     "security": "Security concerns related to the code element.",
 *     "url": "URL for more information about the code element."
 *   }
 * }
 */
class AcDocumentation {

    const KEY_NAME = 'name';
    const KEY_TYPE = 'type';
    const KEY_NAMESPACE = 'namespace';
    const KEY_DESCRIPTION = 'description';
    const KEY_DETAILS = 'details';
    const KEY_CATEGORY = 'category';
    const KEY_VISIBILITY = 'visibility';
    const KEY_DEPRECATED = 'deprecated';
    const KEY_SINCE = 'since';
    const KEY_PARAMETERS = 'parameters';
    const KEY_RETURN = 'return';
    const KEY_THROWS = 'throws';
    const KEY_EXAMPLES = 'examples';
    const KEY_AUTHOR = 'author';
    const KEY_MAINTAINER = 'maintainer';
    const KEY_LAST_MODIFIED = 'lastModified';
    const KEY_FILE = 'file';
    const KEY_TAGS = 'tags';
    const KEY_RELATED = 'related';
    const KEY_SECURITY = 'security';
    const KEY_URL = 'url';

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
     *   "name": "name",
     *   "type": "property",
     *   "description": "The name of the documented element (function, class, etc.)."
     * }
     */
    public string $name = "";

    /**
     * @acDoc {
     *   "name": "type",
     *   "type": "property",
     *   "description": "The type of the code element (e.g., 'class', 'method')."
     * }
     */
    public string $type = "";

    /**
     * @acDoc {
     *   "name": "namespace",
     *   "type": "property",
     *   "description": "The namespace of the code element, if available."
     * }
     */
    public ?string $namespace = null;

    /**
     * @acDoc {
     *   "name": "description",
     *   "type": "property",
     *   "description": "A brief description of the functionality of the code element."
     * }
     */
    public ?string $description = null;

    /**
     * @acDoc {
     *   "name": "details",
     *   "type": "property",
     *   "description": "Additional details about the code element."
     * }
     */
    public ?string $details = null;

    /**
     * @acDoc {
     *   "name": "category",
     *   "type": "property",
     *   "description": "Category of the documentation (e.g., 'API', 'Utility')."
     * }
     */
    public ?string $category = null;

    /**
     * @acDoc {
     *   "name": "visibility",
     *   "type": "property",
     *   "description": "Visibility of the code element (e.g., 'public', 'private')."
     * }
     */
    public ?string $visibility = null;

    /**
     * @acDoc {
     *   "name": "deprecated",
     *   "type": "property",
     *   "description": "Indicates whether the code element is deprecated."
     * }
     */
    public bool $deprecated = false;

    /**
     * @acDoc {
     *   "name": "since",
     *   "type": "property",
     *   "description": "The version of the code element."
     * }
     */
    public ?string $since = null;

    /**
     * @acDoc {
     *   "name": "parameters",
     *   "type": "property",
     *   "description": "List of parameters for methods or functions."
     * }
     */
    public ?array $parameters = [];

    /**
     * @acDoc {
     *   "name": "return",
     *   "type": "property",
     *   "description": "Return type and description for the code element."
     * }
     */
    public ?array $return = [];

    /**
     * @acDoc {
     *   "name": "throws",
     *   "type": "property",
     *   "description": "List of exceptions the code element can throw."
     * }
     */
    public ?array $throws = [];

    /**
     * @acDoc {
     *   "name": "examples",
     *   "type": "property",
     *   "description": "Examples demonstrating the usage of the code element."
     * }
     */
    public ?array $examples = [];

    /**
     * @acDoc {
     *   "name": "author",
     *   "type": "property",
     *   "description": "The author of the code element."
     * }
     */
    public ?string $author = null;

    /**
     * @acDoc {
     *   "name": "maintainer",
     *   "type": "property",
     *   "description": "The maintainer of the code element."
     * }
     */
    public ?string $maintainer = null;

    /**
     * @acDoc {
     *   "name": "lastModified",
     *   "type": "property",
     *   "description": "The date when the code element was last modified."
     * }
     */
    public ?string $lastModified = null;

    /**
     * @acDoc {
     *   "name": "file",
     *   "type": "property",
     *   "description": "The file in which the code element is located."
     * }
     */
    public ?string $file = null;

    /**
     * @acDoc {
     *   "name": "tags",
     *   "type": "property",
     *   "description": "Additional tags associated with the code element."
     * }
     */
    public ?array $tags = [];

    /**
     * @acDoc {
     *   "name": "related",
     *   "type": "property",
     *   "description": "Related code elements or references."
     * }
     */
    public ?array $related = [];

    /**
     * @acDoc {
     *   "name": "security",
     *   "type": "property",
     *   "description": "Security concerns related to the code element."
     * }
     */
    public ?array $security = [];

    /**
     * @acDoc {
     *   "name": "url",
     *   "type": "property",
     *   "description": "URL for more information about the code element."
     * }
     */
    public ?string $url = null;

    /**
     * Constructor method to initialize binding configuration.
     */
    public function __construct() {
        $this->acJsonBindConfig = AcJsonBindConfig::fromJson(jsonData: [
            AcJsonBindConfig::KEY_PROPERY_BINDINGS => [
                self::KEY_NAME => 'name',
                self::KEY_TYPE => 'type',
                self::KEY_NAMESPACE => 'namespace',
                self::KEY_DESCRIPTION => 'description',
                self::KEY_DETAILS => 'details',
                self::KEY_CATEGORY => 'category',
                self::KEY_VISIBILITY => 'visibility',
                self::KEY_DEPRECATED => 'deprecated',
                self::KEY_SINCE => 'since',
                self::KEY_PARAMETERS => 'parameters',
                self::KEY_RETURN => 'return',
                self::KEY_THROWS => 'throws',
                self::KEY_EXAMPLES => 'examples',
                self::KEY_AUTHOR => 'author',
                self::KEY_MAINTAINER => 'maintainer',
                self::KEY_LAST_MODIFIED => 'lastModified',
                self::KEY_FILE => 'file',
                self::KEY_TAGS => 'tags',
                self::KEY_RELATED => 'related',
                self::KEY_SECURITY => 'security',
                self::KEY_URL => 'url',
            ]        
        ]);
    }

    /**
     * Static method to create an instance from JSON data.
     *
     * @acDoc {
     *   "name": "fromJson",
     *   "type": "method",
     *   "description": "Creates an instance of AcDocumentation from a JSON array."
     * }
     */
    public static function fromJson(array $jsonData): AcDocumentation {
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
     * Convert the instance to a JSON array.
     *
     * @acDoc {
     *   "name": "toJson",
     *   "type": "method",
     *   "description": "Converts the instance into a JSON array."
     * }
     */
    public function toJson(): array {
        return AcUtilsJson::createJsonArrayFromInstance(instance: $this);
    }

    /**
     * Convert the instance to a string (JSON format).
     *
     * @acDoc {
     *   "name": "__toString",
     *   "type": "method",
     *   "description": "Converts the instance to a JSON string for easy display."
     * }
     */
    public function __toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }

    /**
     * Return a string representation of the instance (JSON format).
     *
     * @acDoc {
     *   "name": "toString",
     *   "type": "method",
     *   "description": "Returns a JSON string representation of the instance."
     * }
     */
    public function toString(): string {
        return json_encode($this->toJson(), JSON_PRETTY_PRINT);
    }
}
