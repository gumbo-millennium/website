<?php

declare(strict_types=1);

namespace App\Models\Payments;

use App\Casts\MoneyCast;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Mollie Payment settlements.
 *
 * @property int $id
 * @property string $mollie_id
 * @property null|string $reference
 * @property string $status
 * @property \Brick\Money\Money $amount
 * @property \Brick\Money\Money $fees
 * @property \Illuminate\Support\Collection $missing_payments
 * @property \Illuminate\Support\Collection $missing_refunds
 * @property null|string $export_path
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|\Illuminate\Support\Carbon $settled_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Payment> $payments
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Payment> $refunds
 * @method static \Illuminate\Database\Eloquent\Builder|Settlement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Settlement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Settlement query()
 * @mixin \Eloquent
 */
class Settlement extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_settlements';

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'missing_payments' => '[]',
        'missing_refunds' => '[]',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => MoneyCast::class,
        'fees' => MoneyCast::class,
        'settled_at' => 'datetime',
        'missing_payments' => 'collection',
        'missing_refunds' => 'collection',
    ];

    protected $fillable = [
        'mollie_id',
        'reference',
        'status',
        'amount',
        'fees',
        'created_at',
        'settled_at',
        'missing_payments',
        'missing_refunds',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'missing_payments',
        'missing_refunds',
    ];

    /**
     * Payments settled with this settlement.
     */
    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Payment::class,
            table: 'payment_settlement_payment',
            foreignPivotKey: 'settlement_id',
            relatedPivotKey: 'payment_id',
        )->withPivot('amount')->using(PaymentSettlement::class);
    }

    /**
     * Refunds refunded through this settlement.
     */
    public function refunds(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Payment::class,
            table: 'payment_settlement_refunded_payment',
            foreignPivotKey: 'settlement_id',
            relatedPivotKey: 'payment_id',
        )->using(PaymentSettlement::class);
    }
}
