<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BotUserLink extends UuidModel
{
    public const TYPE_USER = 'user';
    public const TYPE_CHANNEL = 'list';

    /**
     * Shorthand to create a link, optionally connected to a user
     * @param string $driver
     * @param string $driverId
     * @param string $type
     * @param null|User $user
     * @return BotUserLink
     */
    public static function createForDriver(string $driver, string $driverId, string $type, ?User $user = null): self
    {
        return self::create([
            'user_id' => optional($user)->id,
            'driver' => $driver,
            'driver_id' => $driverId,
            'type' => $type
        ]);
    }

    /**
     * Scopes to a driver and it's ID
     * @param Builder $query
     * @param string $driver
     * @param string $driverId
     * @return Builder
     * @throws InvalidArgumentException
     */
    public function scopeWhereDriverId(Builder $query, string $driver, string $driverId): Builder
    {
        return $query->where([
            'driver' => $driver,
            'driver_id' => $driverId,
        ]);
    }

    /**
     * Returns the owning user
     * @returns BelongsTo<App\Models\User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
