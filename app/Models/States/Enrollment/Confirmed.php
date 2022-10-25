<?php

declare(strict_types=1);

namespace App\Models\States\Enrollment;

/**
 * Enrollment was confirmed. Can't be deleted automatically.
 */
class Confirmed extends State
{
    /**
     * Name of the state.
     */
    public static $name = 'confirmed';

    public function getTitleAttribute(): string
    {
        return 'Bevestigd';
    }
}
