<?php

namespace AcExtensions;

trait AcNumberExtensions {
    public static function isEven(int|float $number): bool {
        return $number % 2 === 0;
    }

    public static function isOdd(int|float $number): bool {
        return $number % 2 !== 0;
    }

    public static function round(float $number, int $decimals = 2): float {
        return round($number, $decimals);
    }
}

?>