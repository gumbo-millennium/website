<?php

declare(strict_types=1);

namespace App\Models\States\Enrollment;

/**
 * Enrollment was cancelled. Usually implies a refund was given
 */
class Refunded extends Cancelled
{
    /**
     * Name of the state, as human-readable version
     *
     * @var string
     */
    public static $name = 'refunded';

    /**
     * @inheritDoc
     */
    public function getTitleAttribute(): string
    {
        return 'Terugbetaald';
    }
}
