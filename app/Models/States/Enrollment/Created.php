<?php

declare(strict_types=1);

namespace App\Models\States\Enrollment;

/**
 * Enrollment was created, but still needs confirmation.
 */
class Created extends State
{
    /**
     * Name of the state.
     */
    public static $name = 'created';

    public function getTitleAttribute(): string
    {
        return 'Nieuw';
    }
}
