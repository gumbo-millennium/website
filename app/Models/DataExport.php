<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;

/**
 * App\Models\DataExport.
 *
 * @property int $id
 * @property string $token
 * @property int $user_id
 * @property null|string $path
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|\Illuminate\Support\Carbon $completed_at
 * @property \Illuminate\Support\Carbon $expires_at
 * @property null|\Illuminate\Support\Carbon $deleted_at
 * @property-read bool $is_expired
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|DataExport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DataExport newQuery()
 * @method static \Illuminate\Database\Query\Builder|DataExport onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|DataExport query()
 * @method static \Illuminate\Database\Query\Builder|DataExport withTrashed()
 * @method static \Illuminate\Database\Query\Builder|DataExport withoutTrashed()
 * @mixin \Eloquent
 */
class DataExport extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
    ];

    public static function findByToken(int $id, string $token): ?self
    {
        return self::query()
            ->where('token', $token)
            ->find($id);
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function (self $dataExport) {
            $dataExport->expires_at = Date::now()->addDays(Config::get('gumbo.export-expire-days'));
            $dataExport->token = Str::random(20);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getIsExpiredAttribute(): bool
    {
        return ! $this->expires_at || $this->expires_at->isPast();
    }
}
