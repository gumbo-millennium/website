<?php

declare(strict_types=1);

namespace App\Nova\Fields;

use Laravel\Nova\Fields\Number;

/**
 * A seat availability field
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class Seats extends Number
{
    /**
     * Resolve the field's value for display.
     *
     * @param  mixed  $resource
     * @param  string|null  $attribute
     * @return void
     */
    public function resolveForDisplay($resource, $attribute = null)
    {
        // Get value via parent
        parent::resolveForDisplay($resource, $attribute);

        // Show infinity if value is null
        if ($this->value !== null) {
            return;
        }

        $this->value = '∞';
    }
}
