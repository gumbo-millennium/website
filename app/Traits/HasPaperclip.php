<?php

declare(strict_types=1);

namespace App\Traits;

/**
 * Handles the __consruct method to bind paperclip nodes
 * to the element.
 */
trait HasPaperclip
{
    /**
     * Binds the files with paperclip.
     */
    public function __construct(array $attributes = [])
    {
        // Ensure method exists, will be out-optimized when compiling
        assert(method_exists($this, 'bindPaperclip'), sprintf('Failed to find bindPaperclip on %s', static::class));

        // Bind paperclip nodes
        $this->bindPaperclip();

        // Forward call
        parent::__construct($attributes);
    }
}
