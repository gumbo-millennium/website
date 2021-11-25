<?php

declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Date;

/**
 * App\Models\Payment.
 *
 * @property int $id
 * @property string $payable_type
 * @property int $payable_id
 * @property string $status
 * @property string $provider
 * @property string $transaction_id
 * @property int $amount In cents
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|\Illuminate\Support\Carbon $paid_at
 * @property null|\Illuminate\Support\Carbon $expired_at
 * @property null|\Illuminate\Support\Carbon $cancelled_at
 * @property-read Eloquent|Model $payable
 * @method static Builder|Payment newModelQuery()
 * @method static Builder|Payment newQuery()
 * @method static Builder|Payment pending()
 * @method static Builder|Payment query()
 * @mixin \Eloquent
 */
class Payment extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'int',

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
        'amount',
        'status',
        'provider',
        'transaction_id',
    ];

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopePending(Builder $query): void
    {
        $query
            ->whereNull('paid_at')
            ->whereNull('cancelled_at');

        $query->where(fn ($query) => $query->orWhere([
            ['expired_at', '>', Date::now()],
            ['expired_at', '=', null],
        ]));
    }
}
