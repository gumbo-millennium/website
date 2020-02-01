<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Str as SupportStr;

/**
 * String extensions
 */
class Str extends SupportStr
{
    /**
     * Validates given number
     * @param mixed $value
     * @return null|float
     */
    private static function getValidNumber($value): ?float
    {
        // Validate number value
        $number = filter_var($value, FILTER_VALIDATE_FLOAT);

        // Skip if empty
        return ($number === false) ? null : $number;
    }

    /**
     * Formats as Dutch number
     * @param mixed $value
     * @param int $decimals
     * @return void
     */
    public static function number($value, int $decimals = 0): ?string
    {
        // Validate number and return null if empty
        $value = self::getValidNumber($value);

        // Return formatted number, if set
        return ($value === null) ? null :  number_format($value, $decimals, ',', '.');
    }

    // Price formatting
    public static function price($value, bool $decimals = null): ?string
    {
        // Validate number and return null if empty
        $value = self::getValidNumber($value);
        if ($value === null) {
            return null;
        }

        $value /= 100;
        $prefix = ($value < 0) ? '-' : '';

        // Handle round value value
        if ($decimals === false || (($value * 100) % 100 === 0 && $decimals !== true)) {
            return sprintf('%s€ %s,-', $prefix, number_format(abs($value), 0, ',', '.'));
        }

        // Handle decimal value
        return sprintf('%s€ %s', $prefix, number_format(abs($value), 2, ',', '.'));
    }

    /**
     * Returns singular if value is one, plurarl otherwise.
     * @param string $singular
     * @param string $plural
     * @param int $value
     * @return string
     */
    public static function multiple(string $singular, string $plural, int $value): string
    {
        return $value === 1 ? $singular : $plural;
    }
}
