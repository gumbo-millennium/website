<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\FileCategory;

/**
 * Handles the __consruct method to bind paperclip nodes
 * to the element
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
trait HasPaperclip
{

    /**
     * Binds the files with paperclip
     *
     * @param array $attributes
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
