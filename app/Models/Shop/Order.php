<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Events\OrderPaidEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Date;

/**
 * A user's order.
 *
 * @property int $id
 * @property string $number
 * @property int $user_id
 * @property null|string $payment_id
 * @property \Illuminate\Support\Date $created_at
 * @property \Illuminate\Support\Date $updated_at
 * @property null|\Illuminate\Support\Date $expires_at
 * @property null|\Illuminate\Support\Date $paid_at
 * @property null|\Illuminate\Support\Date $shipped_at
 * @property int $price
 * @property-read string $status
 * @property-read \Illuminate\Database\Eloquent\Collection<ProductVariant> $variants
 * @property-read \App\Models\User $user
 */
class Order extends Model
{
    protected $table = 'shop_orders';

    protected $casts = [
        'price' => 'int',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
    ];

    protected $fillable = [
        'price',
        'fee',
    ];

    /**
     * Bind invoice ID handling.
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function (self $order) {
            // Set expiration
            $order->expires_at ??= Date::now()->addDay();

            // Set order number
            $order->number = self::determineOrderNumber($order);
        });

        static::updating(function (self $order) {
            if ($order->paid_at !== null && $order->getOriginal('paid_at') === null) {
                OrderPaidEvent::dispatch($order);
            }
        });
    }

    /**
     * Assigns an order number if not yet set.
     * @return void
     */
    public static function determineOrderNumber(self $order): string
    {
        $targetDate = $order->created_at ?? Date::now();

        // Get invoice number
        $startOfMonth = Date::parse(sprintf(
            'first day of %s %d',
            $targetDate->format('F'),
            $targetDate->year,
        ));

        $orderCount = self::query()
            ->whereBetween('created_at', [$startOfMonth, $targetDate])
            ->when($order->id, fn (Builder $query) => $query->where('id', '<', $order->id))
            ->count();

        // Set invoice ID
        return sprintf(
            '%02d.%02d.%03d',
            $targetDate->century % 100,
            $targetDate->month,
            $orderCount + 1,
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'shop_order_product_variant')
            ->using(OrderProduct::class)
            ->withPivot(['quantity', 'price']);
    }

    public function getStatusAttribute(): string
    {
        if ($this->shipped_at) {
            return 'sent';
        }

        return $this->paid_at ? 'paid' : 'pending';
    }

    /**
     * Eagerly load data used in common views.
     * @return Order
     */
    public function hungry(): self
    {
        return $this->loadMissing([
            'user',
            'variants',
            'variants.product',
        ]);
    }
}
