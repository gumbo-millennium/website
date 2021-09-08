<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use LogicException;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * A role, with some modifications.
 *
 * @property int $id
 * @property string $name
 * @property null|string $title
 * @property string $guard_name
 * @property bool $default
 * @property null|int $conscribo_id
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Permission[] $permissions
 * @property-read \App\Models\User[]|\Illuminate\Database\Eloquent\Collection $users
 * @method static Builder|Role default()
 * @method static Builder|Role newModelQuery()
 * @method static Builder|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role permission($permissions)
 * @method static Builder|Role query()
 * @mixin \Eloquent
 */
class Role extends SpatieRole
{
    /**
     * Groups the system should not remove.
     */
    public const REQUIRED_GROUPS = [
        'guest',
        'member',
        'verified',
        'board',
    ];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'default' => 'bool',
        'conscribo_id' => 'int',
    ];

    /**
     * Prevent deleting of a required model, as a safety net.
     *
     * @throws LogicException
     */
    public static function bootRole(): void
    {
        static::deleting(static function (self $role) {
            if (\in_array($role->name, self::REQUIRED_GROUPS, true)) {
                throw new LogicException("Cannot remove required role {$role->name}");
            }
        });
    }

    /**
     * Filter on default value.
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('default', '1');
    }
}
