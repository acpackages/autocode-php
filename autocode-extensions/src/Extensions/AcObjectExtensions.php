<?php

namespace AcExtensions;

trait AcObjectExtensions {
    public static function changes(array $oldObject, array $newObject): array {
        $result = [];
        
        foreach ($newObject as $key => $value) {
            if (!array_key_exists($key, $oldObject)) {
                $result[$key] = ['old' => null, 'new' => $value, 'change' => 'add'];
            } elseif ($oldObject[$key] !== $value) {
                $result[$key] = ['old' => $oldObject[$key], 'new' => $value, 'change' => 'modify'];
            }
        }
        
        foreach ($oldObject as $key => $value) {
            if (!array_key_exists($key, $newObject)) {
                $result[$key] = ['old' => $value, 'new' => null, 'change' => 'remove'];
            }
        }
        
        return $result;
    }

    public static function clone(array $object): array {
        return json_decode(json_encode($object), true);
    }

    public static function copyFrom(array &$destination, array $source): void {
        foreach ($source as $key => $value) {
            $destination[$key] = $value;
        }
    }

    public static function copyTo(array $source, array &$destination): void {
        foreach ($source as $key => $value) {
            $destination[$key] = $value;
        }
    }

    public static function containsKey(array $object, string $key): bool {
        return array_key_exists($key, $object);
    }    

    public static function filter(array $object, callable $filterFunction): array {
        return array_filter($object, $filterFunction, ARRAY_FILTER_USE_BOTH);
    }

    public static function isEmpty(array $object): bool {
        return empty($object);
    }

    public static function isNotEmpty(array $object): bool {
        return !empty($object);
    }

    public static function isSame(array $objectA, array $objectB): bool {
        return json_encode($objectA) === json_encode($objectB);
    }

    public static function toQueryString(array $object): string {
        return http_build_query($object);
    }
}

?>
