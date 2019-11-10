<?php

declare(strict_types=1);

namespace App\Models\States\Enrollment;

/**
 * Enrollment was seeded, but is not yet paid.
 */
class Seeded extends State
{
    /**
     * @inheritDoc
     */
    public function getTitleAttribute(): string
    {
        return 'Formulier ingevuld';
    }
}
