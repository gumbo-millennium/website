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
    public const NAME = 'paid';

    /**
     * Name of the state, as human-readable version.
     *
     * @var string
     */
    public static $name = self::NAME;

    /**
     * @inheritDoc
     */
    public function getTitleAttribute(): string
    {
        return 'Betaald';
    }
}
