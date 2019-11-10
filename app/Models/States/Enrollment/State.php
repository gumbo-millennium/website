<?php

declare(strict_types=1);

namespace App\Models\States\Enrollment;

use App\Models\States\Traits\HasAttributes;
use Spatie\ModelStates\State as BaseState;

/**
 * Enrollment state. Has no properties
 */
abstract class State extends BaseState
{
    use HasAttributes;

    /**
     * Returns a nice name for this object
     *
     * @return string
     */
    public function getNameAttribute(): string
    {
        return class_basename($this);
    }

    /**
     * Get the title of this status.
     *
     * @return string
     */
    abstract public function getTitleAttribute(): string;
}
