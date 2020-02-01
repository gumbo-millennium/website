<?php

namespace App\Models\States\Traits;

use App\Helpers\Str;

/**
 * Allows Eloquent-like access to the states
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
trait HasAttributes
{
    /**
     * Get the property
     *
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        // Skip if empty
        if (!$key) {
            return;
        }

        // Get proper method
        $keyCaps = Str::studly($key);
        $keyMethod = "get{$keyCaps}Attribute";

        // Check if method exists
        if (method_exists($this, "get{$keyCaps}Attribute")) {
            return $this->$keyMethod();
        }
    }
}
