<?php

namespace AcExtensions;

trait AcArrayExtensions {
    public static function containsKey($key, array $array): bool {
        return in_array($key,array_keys($array));
    }

    public static function difference(array $arr1, array $arr2): array {
        return array_values(array_diff($arr1, $arr2));
    }

    public static function differenceSymmetrical(array $arr1, array $arr2): array {
        return array_values(array_merge(array_diff($arr1, $arr2), array_diff($arr2, $arr1)));
    }

    public static function intersection(array $arr1, array $arr2): array {
        return array_values(array_intersect($arr1, $arr2));
    }

    public static function isEmpty(array $arr): bool {
        return empty($arr);
    }

    public static function isNotEmpty(array $arr): bool {
        return !empty($arr);
    }

    public static function prepend(array $arr, mixed $value): array {
        array_unshift($arr, $value);
        return $arr;
    }

    public static function remove(array $arr, mixed $value): array {
        return array_values(array_filter($arr, fn($item) => $item !== $value));
    }

    public static function removeByIndex(array $arr, int $index): array {
        if (isset($arr[$index])) {
            unset($arr[$index]);
        }
        return array_values($arr);
    }

    public static function union(array $arr1, array $arr2): array {
        return array_values(array_unique(array_merge($arr1, $arr2)));
    }

    public static function toObject(array $arr, string $key): array {
        $result = [];
        foreach ($arr as $item) {
            if (is_array($item) && isset($item[$key])) {
                $result[$item[$key]] = $item;
            }
        }
        return $result;
    }
}

?>
