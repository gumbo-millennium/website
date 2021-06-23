<?php

declare(strict_types=1);

namespace App\Models\States\Traits;

use App\Helpers\Str;

/**
 * Allows Eloquent-like access to the states.
 */
trait HasAttributes
{
    /**
     * Get the property.
     */
    public function __get(string $key)
    {
        // Skip if empty
        if (! $key) {
            return;
        }

        // Get proper method
        $keyCaps = Str::studly($key);
        $keyMethod = "get{$keyCaps}Attribute";

        // Check if method exists
        if (method_exists($this, "get{$keyCaps}Attribute")) {
            return $this->{$keyMethod}();
        }
    }
}
