<?php

declare(strict_types=1);

namespace App\Models\GoogleWallet;

use App\Casts\MoneyCast;
use App\Models\User;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * A Google Wallet Event Object, which is a single ticket on a
 * Google Wallet Event Class. Usually maps to an Enrollment.
 * @property int $id
 * @property int $class_id
 * @property string $wallet_id
 * @property string $subject_type
 * @property int $subject_id
 * @property null|int $owner_id
 * @property string $state
 * @property \App\Enums\Models\GoogleWallet\ReviewStatus $review_status
 * @property null|mixed $review
 * @property null|\Brick\Money\Money $value
 * @property string $ticket_number
 * @property string $ticket_type
 * @property string $barcode
 * @property int $installs
 * @property int $removals
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\GoogleWallet\EventClass $class
 * @property-read int $active_installations
 * @property-read null|\Illuminate\Database\Eloquent\Model $class_subject
 * @property-read EventObject[]|\Illuminate\Database\Eloquent\Collection $objects
 * @property-read null|User $owner
 * @property-read Eloquent|Model $subject
 * @method static \Database\Factories\GoogleWallet\EventObjectFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|EventObject forSubject(\Illuminate\Database\Eloquent\Model $subject)
 * @method static \Illuminate\Database\Eloquent\Builder|EventObject newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventObject newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventObject query()
 * @mixin Eloquent
 */
class EventObject extends Model
{
    use HasFactory;
    use HasGoogleWalletProperties;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'google_wallet_event_objects';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'value' => MoneyCast::class,
        'installs' => 'integer',
        'removals' => 'integer',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'state',
        'value',
        'ticket_number',
        'ticket_type',
        'barcode',
    ];

    /**
     * Ensure only valid wallet objects are persisted to the
     * database.
     */
    public static function booted(): void
    {
        static::creating(function (self $object) {
            throw_if($object->class === null, LogicException::class, 'Wallet Class is required');
        });
    }

    /**
     * The wallet class this object belongs to.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(EventClass::class, 'class_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * The subject the wallet class belongs to.
     */
    public function getClassSubjectAttribute(): ?Model
    {
        return $this->class?->subject;
    }

    /**
     * The number of devices this wallet object is installed on.
     */
    public function getActiveInstallationsAttribute(): int
    {
        return max(($this->redemptions ?? 0) - ($this->removals ?? 0), 0);
    }
}
