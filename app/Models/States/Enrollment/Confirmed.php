<?php

declare(strict_types=1);

namespace App\Models\States\Enrollment;

/**
 * Enrollment was confirmed. Can't be deleted automatically
 */
class Confirmed extends State
{
    /**
     * Name of the state, as human-readable version
     *
     * @var string
     */
    public static $name = 'confirmed';

    /**
     * @inheritDoc
     */
    public function getTitleAttribute(): string
    {
        return 'Bevestigd';
    }
}
