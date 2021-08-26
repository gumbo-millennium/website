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
    public const NAME = 'seeded';

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
        return 'Wachtend op betaling';
    }
}
