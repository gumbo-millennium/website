<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A connection from Telegram to our website, used for the name basically.
 *
 * @property string $id
 * @property string $driver
 * @property string $driver_id
 * @property null|string $name
 * @property-read User $user
 */
class BotUserLink extends UuidModel
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'driver',
        'driver_id',
        'name',
    ];

    /**
     * Sets the name of the ID on the given driver.
     */
    public static function setName(string $platform, string $platformId, ?string $name): void
    {
        self::updateorCreate([
            'driver' => $platform,
            'driver_id' => $platformId,
        ], [
            'name' => $name,
        ]);
    }

    /**
     * Returns the stored name.
     */
    public static function getName(string $platform, string $platformId): string
    {
        return self::where([
            'driver' => $platform,
            'driver_id' => $platformId,
        ])->pluck('name')->first() ?? "#{$platformId}";
    }

    /**
     * Scopes to a driver and it's ID.
     *
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
     * Returns the owning user.
     *
     * @returns BelongsTo<App\Models\User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
