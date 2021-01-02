<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use LogicException;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * A role, with some modifications
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class Role extends SpatieRole
{
    /**
     * Groups the system should not remove
     */
    public const REQUIRED_GROUPS = [
        'guest',
        'member',
        'verified',
        'board',
    ];

    /**
     * Prevent deleting of a required model, as a safety net
     *
     * @param Role $role
     * @return void
     * @throws LogicException
     */
    public static function bootRole(): void
    {
        static::deleting(static function (Role $role) {
            if (\in_array($role->name, self::REQUIRED_GROUPS)) {
                throw new LogicException("Cannot remove required role {$role->name}");
            }
        });
    }

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'default' => 'bool',
        'conscribo_id' => 'int',
    ];

    /**
     * Filter on default value
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('default', '1');
    }
}
