<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BotUserLink extends UuidModel
{
    public const TYPE_USER = 'user';
    public const TYPE_CHANNEL = 'list';

    private const DEFAULT_ICON = 'robot';

    private const DRIVER_ICONS = [
        'telegram' => 'brands/telegram',
        'discord' => 'brands/discord'
    ];

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
     * Returns the owning user
     * @returns BelongsTo<App\Models\User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Returns the icon name
     * @return string
     */
    public function getIconAttribute(): string
    {
        return self::DRIVER_ICONS[$this->driver] ?? self::DRIVER_ICONS['default'];
    }
}
