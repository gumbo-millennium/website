<?php

declare(strict_types=1);

namespace App\Models\States\Enrollment;

/**
 * Enrollment was created, but still needs confirmation
 */
class Created extends State
{
    /**
     * Name of the state, as human-readable version
     *
     * @var string
     */
    public static $name = 'created';

    /**
     * @inheritDoc
     */
    public function getTitleAttribute(): string
    {
        return 'Nieuw';
    }
}
