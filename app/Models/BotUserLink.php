<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BotUserLink extends UuidModel
{
    /**
     * Sets the name of the ID on the given driver
     *
     * @param string $platform
     * @param string $platformId
     * @param string|null $name
     * @return void
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
     * Returns the stored name
     *
     * @param string $platform
     * @param string $platformId
     * @return string
     */
    public static function getName(string $platform, string $platformId): string
    {
        return self::where([
            'driver' => $platform,
            'driver_id' => $platformId,
        ])->pluck('name')->first() ?? "#{$platformId}";
    }

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
     * Scopes to a driver and it's ID
     *
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
     *
     * @returns BelongsTo<App\Models\User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
