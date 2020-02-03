<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * A role, with some modifications
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class Role extends SpatieRole
{
    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'default' => 'bool',
    ];

    /**
     * Filter on default value
     * @param Builder $query
     * @return Builder
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('default', '1');
    }
}
