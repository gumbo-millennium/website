<?php

declare(strict_types=1);

namespace App\Models\States\Enrollment;

/**
 * Enrollment was seeded, but is not yet paid.
 */
class Seeded extends State
{
    /**
     * Name of the state.
     */
    public static $name = 'seeded';

    public function getTitleAttribute(): string
    {
        return 'Wachtend op betaling';
    }
}
