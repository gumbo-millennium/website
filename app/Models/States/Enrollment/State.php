<?php

declare(strict_types=1);

namespace App\Models\States\Enrollment;

use App\Models\States\Traits\HasAttributes;
use Spatie\ModelStates\State as BaseState;
use Spatie\ModelStates\StateConfig;

/**
 * Base Enrollment state, configures itself.
 */
abstract class State extends BaseState
{
    use HasAttributes;

    /**
     * Name of the state.
     */
    public const NAME = '_state';

    /**
     * States not yet confirmed.
     */
    public const PENDING_STATES = [
        Created::class,
        Seeded::class,
    ];

    /**
     * States confirmed.
     */
    public const CONFIRMED_STATES = [
        Confirmed::class,
        Paid::class,
    ];

    /**
     * States that are not eligible for automatic deletion.
     */
    private const STABLE_STATES = [
        Cancelled::class,
        Confirmed::class,
        Paid::class,
    ];

    public static function config(): StateConfig
    {
        return parent::config()
            // Default to Created
            ->default(Created::class)

            // Create → Seeded
            ->allowTransition(Created::class, Seeded::class)

            // Created, Seeded → Confirmed
            ->allowTransition([Created::class, Seeded::class], Confirmed::class)

            // Created, Seeded, Confirmed → Paid
            ->allowTransition([Created::class, Seeded::class, Confirmed::class], Paid::class)

            // Created, Seeded, Confirmed, Paid → Cancelled
            ->allowTransition(
                [Created::class, Seeded::class, Confirmed::class, Paid::class],
                Cancelled::class,
            )

            // Paid, Cancelled → Refunded
            ->allowTransition(
                [Paid::class, Cancelled::class],
                Refunded::class,
            );
    }

    /**
     * Get the title of this status.
     */
    abstract public function getTitleAttribute(): string;

    /**
     * Returns a nice name for this object.
     */
    public function getNameAttribute(): string
    {
        return static::NAME;
    }

    /**
     * Returns if the enrollment is able to expire in this state.
     */
    public function isStable(): bool
    {
        return $this instanceof Cancelled
            || $this instanceof Paid
            || $this instanceof Confirmed;
    }
}
