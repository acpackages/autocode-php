<?php

namespace AcExtensions;

trait AcStringExtensions
{
    public static function getExtension(string $string): string
    {
        return pathinfo($string, PATHINFO_EXTENSION) ?? '';
    }

    public static function isEmpty(string $string): bool
    {
        return trim($string) === '';
    }

    public static function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public static function isNotEmpty(string $string): bool
    {
        return !AcStringExtensions::isEmpty($string);
    }

    public static function isNumeric(string $string): bool
    {
        return is_numeric($string);
    }

    public static function parseJsonToArray(string $string): array
    {
        $decoded = json_decode($string, true);
        return is_array($decoded) ? $decoded : [];
    }

    public static function parseJsonToObject(string $string): object|array
    {
        $decoded = json_decode($string);
        return $decoded ?? new stdClass();
    }

    public static function random(): string
    {
        return bin2hex(random_bytes(8)) . time();
    }

    public static function regexMatch($pattern, $subject, &$matches = []): bool
    {
        preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', $pattern, $groupMatches);
        $groupNames = $groupMatches[1];
        $escapedPattern = preg_quote($pattern, '#');
        $escapedPattern = str_replace(['\{', '\}'], ['{', '}'], $escapedPattern);
        $convertedPattern = preg_replace('/\{[a-zA-Z_][a-zA-Z0-9_]*\}/', '([^\/]+)', $escapedPattern);
        $regex = '#^' . $convertedPattern . '$#';
        if (!preg_match($regex, $subject, $rawMatches)) {
            $matches = [];
            return false;
        }
        array_shift($rawMatches);
        $namedMatches = [];
        foreach ($groupNames as $index => $name) {
            $namedMatches[$name] = $rawMatches[$index] ?? null;
        }
        $matches = $namedMatches;
        return true;
    }

    public static function toCapitalCase(string $string): string
    {
        return ucwords(strtolower($string));
    }
}

?>