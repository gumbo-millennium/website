<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Contracts\Payments\Payable;
use App\Enums\PaymentStatus;
use App\Fluent\Payment as PaymentFluent;
use App\Models\Traits\HasPayments;
use App\Models\User;
use Database\Factories\Shop\OrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Date;
use RuntimeException;

/**
 * App\Models\Shop\Order.
 *
 * @property int $id
 * @property string $number
 * @property int $user_id
 * @property null|string $payment_id
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|\Illuminate\Support\Carbon $expires_at
 * @property null|\Illuminate\Support\Carbon $paid_at
 * @property null|\Illuminate\Support\Carbon $shipped_at
 * @property null|\Illuminate\Support\Carbon $cancelled_at
 * @property int $price
 * @property int $fee
 * @property-read string $payment_status
 * @property-read string $status
 * @property-read \App\Models\Payment[]|\Illuminate\Database\Eloquent\Collection $payments
 * @property-read User $user
 * @property-read \App\Models\Shop\ProductVariant[]|\Illuminate\Database\Eloquent\Collection $variants
 * @method static Builder|Order cancelled()
 * @method static \Database\Factories\Shop\OrderFactory factory(...$parameters)
 * @method static Builder|Order newModelQuery()
 * @method static Builder|Order newQuery()
 * @method static Builder|Order paid()
 * @method static Builder|Order query()
 * @method static Builder|Order unpaid()
 * @method static Builder|Order whereCancelled()
 * @method static Builder|Order whereExpired()
 * @method static Builder|Order wherePaid()
 * @mixin \Eloquent
 */
class Order extends Model implements Payable
{
    use HasFactory;
    use HasPayments;

    protected $table = 'shop_orders';

    protected $casts = [
        'price' => 'int',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'cancelled_at' => 'datetime',
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
    }

    /**
     * Assigns an order number if not yet set.
     * @return void
     */
    public static function determineOrderNumber(self $order): string
    {
        // Get target
        $targetDate = $order->created_at ?? Date::now();

        // Determine prefix
        $datePrefix = sprintf('%02d.%02d', $targetDate->format('y'), $targetDate->month);

        // Find all orders of this period
        $orderNumbers = self::query()
            ->where('number', 'LIKE', "{$datePrefix}.%")
            ->pluck('number');

        // Find next order number
        $orderCount = $orderNumbers->count();
        $safetyMargin = 0;
        $attemptsLeft = 100;

        do {
            $orderNumber = sprintf('%s.%03d', $datePrefix, $orderCount + $safetyMargin + 1);

            if (! $orderNumbers->contains($orderNumber)) {
                return $orderNumber;
            }

            $safetyMargin++;
        } while ($attemptsLeft-- > 0);

        // Throw exception if no order number found
        throw new RuntimeException('Could not find an order number.');
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return new OrderFactory();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'shop_order_product_variant')
            ->withoutGlobalScopes()
            ->using(OrderProduct::class)
            ->withPivot(['quantity', 'price']);
    }

    public function getStatusAttribute(): string
    {
        if ($this->paid_at) {
            return PaymentStatus::PAID;
        }
        if ($this->cancelled_at) {
            return PaymentStatus::CANCELLED;
        }
        if ($this->expires_at !== null && $this->expires_at < Date::now()) {
            return PaymentStatus::EXPIRED;
        }

        return PaymentStatus::OPEN;
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

    public function toPayment(): PaymentFluent
    {
        $payment = PaymentFluent::make()
            ->withDescription("Webshop bestelling {$this->number}")
            ->withModel($this)
            ->withNumber($this->number)
            ->withUser($this->user);

        foreach ($this->variants as $variant) {
            $payment->addLine(
                $variant->display_name,
                $variant->pivot->price,
                $variant->pivot->quantity,
            );
        }

        $payment->addLine(__('Fees'), (int) $this->fee);

        throw_unless($payment->getSum() === $this->price, RuntimeException::class, 'Price mismatch');

        return $payment;
    }

    public function scopeUnpaid(Builder $query): void
    {
        $query
            ->whereNull('paid_at')
            ->whereNull('cancelled_at');
    }

    public function scopePaid(Builder $query): void
    {
        $query
            ->whereNotNull('paid_at')
            ->whereNull('cancelled_at');
    }

    public function scopeCancelled(Builder $query): void
    {
        $query
            ->whereNotNull('cancelled_at');
    }
}
