<?php
namespace AcWeb\ApiDocs\Swagger;
use AcWeb\Enums\AcEnumWebHook;
use AcWeb\ApiDocs\Models\AcApiDoc;
use AcWeb\ApiDocs\Models\AcApiDocPath;
use AcWeb\ApiDocs\Models\AcApiDocModel;
use AcWeb\Core\AcWeb;
use AcWeb\Models\AcWebHookCreatedArgs;
use Autocode\AcHooks;
use ReflectionClass;
use ReflectionEnum;
use ReflectionEnumUnitCase;
use ReflectionNamedType;
use ReflectionProperty;
class AcApiSwagger
{
    public AcApiDoc $acApiDoc;
    public function __construct()
    {
    }

    public function initialize($method, $args)
    {

    }

    public function generateJson()
    {
        $result = ["openapi" => "3.0.4", "components"=>["schemas"=>[]]];
        $docsJson = $this->acApiDoc->toJson();
        if (isset($docsJson[AcApiDoc::KEY_SERVERS])) {
            if (sizeof($docsJson[AcApiDoc::KEY_SERVERS]) > 0) {
                $result["servers"] = $docsJson[AcApiDoc::KEY_SERVERS];
            }
        }
        if (isset($docsJson[AcApiDoc::KEY_MODELS])) {
            if (sizeof($docsJson[AcApiDoc::KEY_MODELS]) > 0) {
                
                foreach ($docsJson[AcApiDoc::KEY_MODELS] as $model) {
                    $result["components"]["schemas"][$model[AcApiDocModel::KEY_NAME]] = [
                        "properties" => $model[AcApiDocModel::KEY_PROPERTIES]
                    ];
                }
            }
        }
        if (isset($docsJson[AcApiDoc::KEY_PATHS])) {
            if (sizeof($docsJson[AcApiDoc::KEY_PATHS]) > 0) {
                $result["paths"] = [];
                foreach ($docsJson[AcApiDoc::KEY_PATHS] as $path) {
                    $pathDetails = $path;
                    unset($pathDetails[AcApiDocPath::KEY_URL]);
                    $result["paths"][$path[AcApiDocPath::KEY_URL]] = $pathDetails;
                }
            }
        }
        if (isset($docsJson[AcApiDoc::KEY_TAGS])) {
            if (sizeof($docsJson[AcApiDoc::KEY_TAGS]) > 0) {
                $result["tags"] = $docsJson[AcApiDoc::KEY_TAGS];
            }
        }
        return $result;
    }

    public static function generateModelSchema(string $className, array &$components): array
    {
        if (isset($components[$className])) {
            return ['$ref' => "#/components/schemas/{$components[$className]['title']}"];
        }

        $refClass = new ReflectionClass(objectOrClass: $className);
        $schemaName = $refClass->getShortName();
        $schema = [
            'type' => 'object',
            'title' => $schemaName,
            'properties' => [],
        ];

        // Pull in any default property values
        $defaults = $refClass->getDefaultProperties();

        foreach ($refClass->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $propName = $prop->getName();
            $type = $prop->getType();
            $propSchema = [];

            // Determine nullable + underlying types
            $allowsNull = false;
            $types = [];
            if ($type instanceof ReflectionUnionType) {
                foreach ($type->getTypes() as $t) {
                    if ($t->getName() === 'null') {
                        $allowsNull = true;
                    } else {
                        $types[] = $t;
                    }
                }
            } elseif ($type instanceof ReflectionNamedType) {
                $allowsNull = $type->allowsNull();
                $types = [$type];
            }

            // If no type declared, default to string
            if (count($types) === 0) {
                $types[] = new ReflectionNamedType('string', true);
                $allowsNull = true;
            }

            // We only support a single non‑null type in this generator
            /** @var ReflectionNamedType $t */
            $t = $types[0];
            $name = $t->getName();

            if ($t->isBuiltin()) {
                // Map PHP built‑ins to JSON Schema types
                switch ($name) {
                    case 'int':
                    case 'integer':
                        $propSchema['type'] = 'integer';
                        break;
                    case 'float':
                    case 'double':
                        $propSchema['type'] = 'number';
                        break;
                    case 'bool':
                    case 'boolean':
                        $propSchema['type'] = 'boolean';
                        break;
                    case 'array':
                        $propSchema['type'] = 'array';
                        // Without docblocks we can’t infer item types… leave generic
                        $propSchema['items'] = ['type' => 'object'];
                        break;
                    case 'string':
                    default:
                        $propSchema['type'] = 'string';
                        break;
                }
            } else {
                // It’s a class: either an enum or a nested model
                if (is_subclass_of($name, \UnitEnum::class)) {
                    // PHP 8.1+ enum
                    $reflectionEnum = new ReflectionEnum($name);
                    $backingType = $reflectionEnum->getBackingType();
                    if ($backingType) {
                        // backed enum: use underlying type
                        $btName = $backingType->getName();
                        $propSchema['type'] = match ($btName) {
                            'int', 'integer' => 'integer',
                            'string' => 'string',
                            default => 'string',
                        };
                    } else {
                        // pure enum: treat as string
                        $propSchema['type'] = 'string';
                    }
                    // list out all possible enum values
                    $propSchema['enum'] = array_map(
                        fn(ReflectionEnumUnitCase $c) => $c->getBackingValue() ?? $c->getName(),
                        $reflectionEnum->getCases()
                    );
                } else {
                    // Nested object: recurse and add to components
                    $ref = self::generateModelSchema($name, $components);
                    $propSchema = $ref;
                }
            }

            // Nullable?
            if ($allowsNull) {
                $propSchema['nullable'] = true;
            }

            // Default value?
            if (array_key_exists($propName, $defaults)) {
                $default = $defaults[$propName];
                // Only JSON‑serializable defaults
                if (
                    is_null($default)
                    || is_scalar($default)
                    || (is_array($default) && json_encode($default) !== false)
                ) {
                    $propSchema['default'] = $default;
                }
            }

            $schema['properties'][$propName] = $propSchema;
        }

        // Register and return a $ref
        $components[$className] = $schema;
        return ['$ref' => "#/components/schemas/{$schemaName}"];
    }
}
?>