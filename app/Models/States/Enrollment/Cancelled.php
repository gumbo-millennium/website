<?php

declare(strict_types=1);

namespace App\Models\States\Enrollment;

/**
 * Enrollment was cancelled. Usually implies a refund was given
 */
class Cancelled extends State
{
    /**
     * @inheritDoc
     */
    public function getTitleAttribute(): string
    {
        return 'Geannuleerd';
    }
}
