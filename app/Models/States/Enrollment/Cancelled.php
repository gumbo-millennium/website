<?php

declare(strict_types=1);

namespace App\Models\States\Enrollment;

/**
 * Enrollment was cancelled. Usually implies a refund was given.
 */
class Cancelled extends State
{
    /**
     * Name of the state.
     */
    public static $name = 'cancelled';

    public function getTitleAttribute(): string
    {
        return 'Geannuleerd';
    }
}
