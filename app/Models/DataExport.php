<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
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
 * @property-read string $file_name
 * @property-read bool $is_expired
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\DataExportFactory factory(...$parameters)
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
    use HasFactory;
    use Prunable;
    use SoftDeletes;

    /**
     * The attributes that should be cast.
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
            $dataExport->expires_at = Date::now()->add(Config::get('gumbo.retention.data-exports'));
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

    public function getFileNameAttribute(): string
    {
        $extension = Str::afterLast($this->path, '.');

        $localName = __('Data Export :name (:date)', [
            'name' => Str::ascii($this->user?->name),
            'date' => $this->created_at->format('Y-m-d H:i:s'),
        ]);

        return "{$localName}.{$extension}";
    }

    /**
     * Get the prunable model query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function prunable()
    {
        return $this->query()
            ->where('expires_at', '<', Date::today()->subMonths(3));
    }
}
