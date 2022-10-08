<?php

declare(strict_types=1);

namespace App\Models\GoogleWallet;

use App\Enums\Models\GoogleWallet\ReviewStatus;
use App\Helpers\Str;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\URL;
use LogicException;

/**
 * A Google Wallet Event Class, which describes a single event,
 * which has Objects that describe individual tickets.
 * Usually mapped to an Activity.
 * @property int $id
 * @property string $wallet_id
 * @property string $subject_type
 * @property int $subject_id
 * @property \App\Enums\Models\GoogleWallet\ReviewStatus $review_status
 * @property null|mixed $review
 * @property string $name
 * @property null|string $location_name
 * @property null|string $location_address
 * @property \Illuminate\Support\Carbon $start_time
 * @property \Illuminate\Support\Carbon $end_time
 * @property null|string $uri
 * @property null|string $hero_image
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\GoogleWallet\EventObject[]|\Illuminate\Database\Eloquent\Collection $objects
 * @property-read Eloquent|Model $subject
 * @method static \Database\Factories\GoogleWallet\EventClassFactory factory(...$parameters)
 * @method static Builder|EventClass forSubject(\Illuminate\Database\Eloquent\Model $subject)
 * @method static Builder|EventClass newModelQuery()
 * @method static Builder|EventClass newQuery()
 * @method static Builder|EventClass query()
 * @mixin Eloquent
 */
class EventClass extends Model
{
    use HasFactory;
    use HasGoogleWalletProperties;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'google_wallet_event_classes';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'review_status' => ReviewStatus::class,
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'location_name',
        'location_address',
        'start_time',
        'end_time',
        'uri',
        'hero_image',
    ];

    /**
     * The wallet objects in this class.
     */
    public function objects(): HasMany
    {
        return $this->hasMany(EventObject::class, 'class_id');
    }

    /**
     * Create a proper random wallet ID.
     * @throws LogicException
     */
    public function assignWalletId(): void
    {
        if ($this->exists) {
            throw new LogicException('Cannot assign wallet ID to existing object');
        }

        // Create a proper ID, with some randomness.
        // If creating for an Activity(id: 285), the ID will look like:
        // AC0285skyu2ngt
        $this->wallet_id = sprintf(
            '%s_%s%04d%s',
            Str::of(md5(URL::to('/')))->lower()->substr(0, 5),
            Str::of(class_basename($this->subject))->studly()->upper()->substr(0, 2),
            $this->subject->id,
            Str::of(Str::random(8))->lower()->ascii(),
        );
    }
}
