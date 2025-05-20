<?php

namespace Autocode;

use Exception;
use ReflectionClass;

require_once 'AcLogger.php';

class Autocode {
    private static array $uniqueIds = [];
    public static ?AcLogger $logger;
    private static string $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    
    public static function enumToObject(string $enumClass): array {
        if (!class_exists($enumClass)) {
            throw new Exception("Enum class '$enumClass' not found.");
        }

        $reflection = new ReflectionClass($enumClass);
        return $reflection->getConstants();
    }

    public static function isBrowser(): bool {
        return isset($_SERVER['HTTP_USER_AGENT']);
    }

    public static function uniqueId(): string {
        $id = "";
        try {
            $timestamp = dechex(time());
            $randomPart = substr(bin2hex(random_bytes(8)), 0, 16);
            $id = "simId_" . self::generateRandomString() . $timestamp . $randomPart;

            if (isset(self::$uniqueIds[$timestamp])) {
                while (isset(self::$uniqueIds[$timestamp][$id])) {
                    $randomPart = substr(bin2hex(random_bytes(8)), 0, 16);
                    $id = "simId_" . self::generateRandomString() . $timestamp . $randomPart;
                }
            } else {
                self::$uniqueIds[$timestamp] = [];
            }
            self::$uniqueIds[$timestamp][$id] = $id;
        } catch (Exception $ex) {
            // Handle error
        }
        return $id;
    }

    public static function generateRandomString(int $length = 10): string {
        $result = '';
        try {
            for ($i = 0; $i < $length; $i++) {
                $randomIndex = random_int(0, strlen(self::$characters) - 1);
                $result .= self::$characters[$randomIndex];
            }
        } catch (Exception $ex) {
            // Handle error
        }
        return $result;
    }

    public static function getClassNameFromInstance(object $instance): string {
        return (new ReflectionClass($instance))->getShortName();
    }

    public static function validPrimaryKey(mixed $value): bool {
        if ($value !== null && $value !== '') {
            if (is_string($value) && $value !== "0") {
                return true;
            }
            if (is_numeric($value) && $value != 0) {
                return true;
            }
        }
        return false;
    }

    public static function validValue(mixed $value): bool {
        return $value !== null;
    }

    public static function uuid(){
        $data = openssl_random_pseudo_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Set version to 4
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Set variant to RFC 4122

    return vsprintf('%08s-%04s-%04s-%04s-%12s', str_split(bin2hex($data), 4));
    }
}
