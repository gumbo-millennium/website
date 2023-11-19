<?php

declare(strict_types=1);

namespace App\Models\Minisite;

use App\Models\Role;
use App\Models\Traits\HasResponsibleUsers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Minisite\Site.
 *
 * @property int $id
 * @property string $domain
 * @property string $name
 * @property int $enabled
 * @property null|int $group_id
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|int $created_by_id
 * @property null|int $updated_by_id
 * @property-read null|\App\Models\User $created_by
 * @property-read null|Role $group
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Minisite\SitePage> $pages
 * @property-read null|\App\Models\User $updated_by
 * @method static \Database\Factories\Minisite\SiteFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Site newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Site newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Site query()
 * @mixin \Eloquent
 */
class Site extends Model
{
    use HasFactory;
    use HasResponsibleUsers;

    /**
     * The table associated with the model.
     */
    protected $table = 'minisites';

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'enabled' => 'bool',
    ];

    public function pages(): HasMany
    {
        return $this->hasMany(SitePage::class, 'site_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'group_id');
    }
}
