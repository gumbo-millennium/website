<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Facades\Payments;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Date;

/**
 * App\Models\Payment.
 *
 * @property int $id
 * @property string $payable_type
 * @property int $payable_id
 * @property null|int $user_id
 * @property string $provider
 * @property null|string $transaction_id
 * @property int $price In cents
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|\Illuminate\Support\Carbon $paid_at
 * @property null|\Illuminate\Support\Carbon $expired_at
 * @property null|\Illuminate\Support\Carbon $cancelled_at
 * @property-read bool $is_stable
 * @property-read string $status
 * @property-read Eloquent|Model $payable
 * @property-read null|\App\Models\User $user
 * @method static Builder|Payment newModelQuery()
 * @method static Builder|Payment newQuery()
 * @method static Builder|Payment pending()
 * @method static Builder|Payment query()
 * @method static Builder|Payment whereTransactionId(string $provider, string $platformId)
 * @mixin Eloquent
 */
class Payment extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'int',

        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'provider',
        'transaction_id',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'price',
        'status',
        'provider',
        'transaction_id',
    ];

    public static function boot()
    {
        parent::boot();

        self::saving(function (self $model) {
            $model ??= Payments::getDefault();
        });
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeWhereTransactionId(Builder $query, string $provider, string $platformId): void
    {
        $query->where([
            'provider' => $provider,
            'transaction_id' => $platformId,
        ]);
    }

    public function scopePending(Builder $query): void
    {
        $query
            ->whereNull('paid_at')
            ->whereNull('cancelled_at');

        $query->where(
            fn ($query) => $query
                ->orWhere('expired_at', '>', Date::now())
                ->orWhereNull('expired_at'),
        );
    }

    public function getStatusAttribute(): string
    {
        if ($this->transaction_id === null) {
            return PaymentStatus::PENDING;
        }

        if ($this->paid_at !== null) {
            return PaymentStatus::PAID;
        }

        if ($this->cancelled_at !== null) {
            return PaymentStatus::CANCELLED;
        }

        if ($this->expired_at !== null && $this->expired_at < Date::now()) {
            return PaymentStatus::EXPIRED;
        }

        return PaymentStatus::OPEN;
    }

    public function getIsStableAttribute(): bool
    {
        return in_array($this->status, PaymentStatus::STABLE_STATES, true);
    }
}
