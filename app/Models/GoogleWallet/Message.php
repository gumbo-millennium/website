<?php

declare(strict_types=1);

namespace App\Models\GoogleWallet;

use App\Models\ActivityMessage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * App\Models\GoogleWallet\Message.
 *
 * @property int $id
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property string $header
 * @property string $body
 * @property \Illuminate\Support\Carbon $start_time
 * @property null|\Illuminate\Support\Carbon $end_time
 * @property int $activity_message_id
 * @property-read ActivityMessage $activityMessage
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GoogleWallet\EventClass> $eventClasses
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GoogleWallet\EventObject> $eventObjects
 * @method static \Illuminate\Database\Eloquent\Builder|Message newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Message newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Message query()
 * @mixin \Eloquent
 */
class Message extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'google_wallet_messages';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'header',
        'body',
        'start_time',
        'end_time',
        'activity_message_id',
    ];

    public function activityMessage(): BelongsTo
    {
        return $this->belongsTo(ActivityMessage::class);
    }

    public function eventClasses(): MorphToMany
    {
        return $this->morphedByMany(EventClass::class, 'object', 'google_wallet_message_morphs');
    }

    public function eventObjects(): MorphToMany
    {
        return $this->morphedByMany(EventObject::class, 'object', 'google_wallet_message_morphs');
    }
}
