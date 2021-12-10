<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;

/**
 * App\Models\WebcamUpdate.
 *
 * @property int $id
 * @property int $webcam_id
 * @property string $ip
 * @property string $user_agent
 * @property null|string $path
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property-read bool $is_expired
 * @property-read string $name
 * @property-read \App\Models\Webcam $webcam
 * @method static \Database\Factories\WebcamUpdateFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|WebcamUpdate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebcamUpdate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebcamUpdate query()
 * @mixin \Eloquent
 */
class WebcamUpdate extends Model
{
    use HasFactory;

    public const STORAGE_LOCATION = 'webcam_updates/images';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'webcam_id' => 'int',
    ];

    /**
     * The attributes that should be visible in serialization.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'webcam.name',
        'created_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'webcam_id',
        'ip',
        'user_agent',
        'path',
    ];

    public static function boot(): void
    {
        parent::boot();

        self::deleting(static function (self $webcamUpdate) {
            if ($webcamUpdate->path && Storage::exists($webcamUpdate->path)) {
                Storage::delete($webcamUpdate->path);
            }
        });
    }

    public function webcam(): BelongsTo
    {
        return $this->belongsTo(Webcam::class);
    }

    public function getNameAttribute(): string
    {
        return sprintf('%s @ %s', $this->webcam->name, $this->created_at->format('Y-m-d H:i:s'));
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->created_at < Date::now()->subHours(3);
    }
}
