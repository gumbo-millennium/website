<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Webcam.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property-read bool $is_expired
 * @property-read null|string $path
 * @property-read null|\App\Models\WebcamUpdate $lastUpdate
 * @property-read \App\Models\WebcamUpdate[]|\Illuminate\Database\Eloquent\Collection $updates
 * @method static \Illuminate\Database\Eloquent\Builder|SluggableModel findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|Webcam newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Webcam newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Webcam query()
 * @method static \Illuminate\Database\Eloquent\Builder|SluggableModel whereSlug(string $slug)
 * @mixin \Eloquent
 */
class Webcam extends SluggableModel
{
    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'lastUpdate',
    ];

    public static function boot(): void
    {
        parent::boot();

        self::saving(static function (self $webcam) {
            if ($webcam->command) {
                $webcam->command = Str::finish(Str::slug($webcam->command), 'cam');
            }
        });

        self::deleting(static function (self $webcam) {
            foreach ($webcam->updates as $update) {
                $update->delete();
            }
        });
    }

    /**
     * Returns a sluggable definition for this model.
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }

    /**
     * Webcam updates.
     */
    public function updates(): HasMany
    {
        return $this->hasMany(WebcamUpdate::class)->latest();
    }

    public function lastUpdate(): HasOne
    {
        return $this->hasOne(WebcamUpdate::class)->latest();
    }

    public function getPathAttribute(): ?string
    {
        return optional($this->lastUpdate)->path;
    }

    public function getIsExpiredAttribute(): bool
    {
        return ! $this->lastUpdate || $this->lastUpdate->is_expired;
    }
}
