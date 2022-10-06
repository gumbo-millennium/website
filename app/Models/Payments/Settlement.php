<?php

declare(strict_types=1);

namespace App\Models\Payments;

use App\Casts\MoneyCast;
use App\Models\Enrollment;
use App\Models\Shop\Order;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Mollie Payment settlements.
 *
 * @property int $id
 * @property string $mollie_id
 * @property null|string $reference
 * @property string $status
 * @property Money $amount
 * @property null|string $export_path
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|\Illuminate\Support\Carbon $settled_at
 * @property-read Enrollment[]|\Illuminate\Database\Eloquent\Collection $enrollments
 * @property-read \Illuminate\Database\Eloquent\Collection|Order[] $shopOrder
 * @method static \Database\Factories\Payments\SettlementFactory factory(...$parameters)
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
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => MoneyCast::class,
        'settled_at' => 'datetime',
    ];

    protected $fillable = [
        'mollie_id',
        'reference',
        'status',
        'amount',
        'created_at',
        'settled_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'mollie_id',
    ];

    /**
     * Get all of the enrollments that are part of this settlement.
     * @return Enrollment[]|MorphToMany
     */
    public function enrollments(): MorphToMany
    {
        return $this->morphedByMany(Enrollment::class, 'subject')
            ->using(SettlementSubject::class)
            ->as('settlement');
    }

    /**
     * Get all shop orders that are part of this settlement.
     * @return MorphToMany|Order[]
     */
    public function shopOrders(): MorphToMany
    {
        return $this->morphedByMany(Order::class, 'subject')
            ->using(SettlementSubject::class)
            ->as('settlement');
    }
}
