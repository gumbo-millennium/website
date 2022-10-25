<?php

declare(strict_types=1);

namespace App\Models\States\Enrollment;

/**
 * Enrollment was cancelled. Usually implies a refund was given.
 */
class Refunded extends Cancelled
{
    /**
     * Name of the state.
     */
    public static $name = 'refunded';

    public function getTitleAttribute(): string
    {
        return 'Terugbetaald';
    }
}
