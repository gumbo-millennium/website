<?php

declare(strict_types=1);

namespace App\Models\Payments;

use App\Casts\MoneyCast;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int settlement_id
 * @property int payment_id
 * @property Money amount
 * @property-read Payment payment
 * @property-read Settlement settlement
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
