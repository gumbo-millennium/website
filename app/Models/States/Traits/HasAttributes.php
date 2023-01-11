<?php

declare(strict_types=1);

namespace App\Models\States\Traits;

use App\Helpers\Str;

/**
 * Allows Eloquent-like access to the states.
 */
trait HasAttributes
{
    private function getAttributeMethod(string $name): string
    {
        $keyCaps = Str::studly($name);

        return "get{$keyCaps}Attribute";
    }

    private function hasAttribute(string $name): bool
    {
        // Get proper method
        $keyMethod = $this->getAttributeMethod($name);

        // Check if method exists
        return method_exists($this, $keyMethod);
    }

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
        if ($this->hasAttribute($key)) {
            return $this->{$this->getAttributeMethod($key)}();
        }
    }

    public function __isset($key)
    {
        return $this->hasAttribute($key);
    }
}
