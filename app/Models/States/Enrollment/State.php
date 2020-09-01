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
     * Name of the state
     */
    public const NAME = '_state';

    /**
     * States that are not eligible for automatic deletion
     */
    private const STABLE_STATES = [
        Cancelled::class,
        Confirmed::class,
        Paid::class,
    ];

    /**
     * Returns a nice name for this object
     * @return string
     */
    public function getNameAttribute(): string
    {
        return static::NAME;
    }

    /**
     * Returns if the enrollment is able to expire in this state
     * @return bool
     */
    public function isStable(): bool
    {
        return $this->isOneOf(self::STABLE_STATES);
    }

    /**
     * Get the title of this status.
     * @return string
     */
    abstract public function getTitleAttribute(): string;
}
