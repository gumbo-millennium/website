<?php

declare(strict_types=1);

namespace App\Models\States\Enrollment;

/**
 * Enrollment was paid and confirmed. Can't be deleted automatically.
 */
class Paid extends Confirmed
{
    /**
     * Name of the state.
     */
    public static $name = 'paid';

    public function getTitleAttribute(): string
    {
        return 'Betaald';
    }
}
