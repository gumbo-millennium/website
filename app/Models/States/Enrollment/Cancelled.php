<?php

declare(strict_types=1);

namespace App\Models\States\Enrollment;

/**
 * Enrollment was cancelled. Usually implies a refund was given
 */
class Cancelled extends State
{
    /**
     * Name of the state
     */
    public const NAME = 'cancelled';

    /**
     * Name of the state, as human-readable version
     *
     * @var string
     */
    public static $name = self::NAME;

    /**
     * @inheritDoc
     */
    public function getTitleAttribute(): string
    {
        return 'Geannuleerd';
    }
}
