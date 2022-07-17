<?php

declare(strict_types=1);

namespace App\Models\Webcam;

use App\Helpers\Str;
use App\Models\SluggableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Webcam\Camera.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $command
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property-read null|\App\Models\Webcam\Device $device
 * @property-read bool $is_expired
 * @property-read null|string $path
 * @method static \Database\Factories\Webcam\CameraFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|SluggableModel findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|Camera newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Camera newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Camera query()
 * @method static \Illuminate\Database\Eloquent\Builder|SluggableModel whereSlug(string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|SluggableModel withUniqueSlugConstraints(\Illuminate\Database\Eloquent\Model $model, string $attribute, array $config, string $slug)
 * @mixin \Eloquent
 */
class Camera extends SluggableModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'webcam_cameras';

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'device',
    ];

    /**
     * Ensure all telegram commands end with 'cam'.
     */
    public static function boot(): void
    {
        parent::boot();

        self::saving(static function (self $webcam) {
            if ($webcam->command) {
                $webcam->command = Str::finish(Str::slug($webcam->command), 'cam');
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
     * The device used for this camera.
     */
    public function device(): HasOne
    {
        return $this->hasOne(Device::class);
    }

    /**
     * Returns the path of the device connected to this device.
     */
    public function getPathAttribute(): ?string
    {
        return $this->device?->path;
    }

    /**
     * Check if the device connected to this webcam has an expired image.
     * Returns false if no image is specified.
     */
    public function getIsExpiredAttribute(): bool
    {
        return (bool) $this->device?->is_expired;
    }
}
