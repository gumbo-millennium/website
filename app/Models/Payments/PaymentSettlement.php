<?php

declare(strict_types=1);

namespace App\Models\Payments;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\Payments\PaymentSettlement.
 *
 * @property \Brick\Money\Money $amount
 * @property-read null|\App\Models\Payments\Settlement $settlement
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentSettlement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentSettlement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentSettlement query()
 * @mixin \Eloquent
 */
class PaymentSettlement extends Pivot
{
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => MoneyCast::class,
    ];

    /**
     * The associated settlement this payment occurs in.
     */
    public function settlement(): BelongsTo
    {
        return $this->belongsTo(Settlement::class);
    }

    /**
     * The associated payment that was paid out or refunded.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
