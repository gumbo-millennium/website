<?php

declare(strict_types=1);

namespace App\Models\Webcam;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Date;

/**
 * App\Models\Webcam\Device.
 *
 * @property int $id
 * @property string $device
 * @property string $name
 * @property null|string $path
 * @property int $owner_id
 * @property null|int $camera_id
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property-read null|\App\Models\Webcam\Camera $camera
 * @property-read bool $is_expired
 * @property-read User $owner
 * @method static \Database\Factories\Webcam\DeviceFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Device newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Device newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Device query()
 * @mixin \Eloquent
 */
class Device extends Model
{
    use HasFactory;

    public const STORAGE_FOLDER = 'system/webcam-devices';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'webcam_devices';

    /**
     * The camera associated with this device.
     */
    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }

    /**
     * The owner of this device, the only one allowed to upload to it.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Returns true if this image is too old to be reliably used.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->updated_at < Date::now()->subHours(24);
    }
}
