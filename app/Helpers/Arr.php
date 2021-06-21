<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Arr as SupportArr;

/**
 * Array extensions.
 */
class Arr extends SupportArr
{
    public static function implode($value): string
    {
        // No 'and' needed
        if (count($value) <= 1) {
            return implode(', ', $value);
        }

        // Pull off last item
        $last = array_pop($value);

        // Merge them
        return implode(', ', $value) . ' en ' . $last;
    }
}
