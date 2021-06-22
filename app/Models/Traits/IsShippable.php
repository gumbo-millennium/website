<?php

declare(strict_types=1);

namespace App\Models\Traits;

trait IsShippable
{
    use IsPayable;

    /**
     * Returns the field to store when the object was shipped.
     */
    public function getShippedAtField(): string
    {
        return 'shipped_at';
    }
}
