<?php

declare(strict_types=1);

namespace App\Models\States\Enrollment;

/**
 * Enrollment was created, but still needs confirmation
 */
class Created extends State
{
    /**
     * @inheritDoc
     */
    public function getTitleAttribute(): string
    {
        return 'Nieuw';
    }
}
