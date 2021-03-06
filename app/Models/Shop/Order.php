<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Contracts\Payments\PayableModel;
use App\Models\Traits\IsPayable;
use App\Models\User;
use App\Services\Payments\Order as FluentOrder;
use App\Services\Payments\OrderLine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\URL;

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
class Order extends Model implements PayableModel
{
    use IsPayable;

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

    /**
     * Returns a Mollie order from this model.
     */
    public function toMollieOrder(): FluentOrder
    {
        $order = FluentOrder::make($this->price, $this->number);

        $address = $this->getPaymentAddressForUser($this->user);
        $order
            ->billingAddress($address)
            ->shippingAddress($address);

        foreach ($this->variants as $variant) {
            $order->addLine(
                OrderLine::make(
                    $variant->display_name,
                    $variant->pivot->quantity,
                    $variant->pivot->price,
                )
                    ->sku($variant->sku)
                    ->imageUrl(URL::to($variant->valid_image_url))
                    ->productUrl($variant->url),
            );
        }

        $order->addLine(
            OrderLine::make(
                'Transactiekosten',
                1,
                (int) $this->fee,
                'surcharge',
            ),
        );

        $order
            ->redirectUrl(URL::route('shop.order.pay-return', $this));

        $isLocal = in_array(parse_url(URL::to('/'), PHP_URL_HOST), [
            'localhost',
            '127.0.0.1',
            '[::1]',
        ], true);

        if (! $isLocal) {
            $order
                ->webhookUrl(URL::route('api.webhooks.shop'));
        }

        $order
            ->method('ideal')
            ->locale('nl_NL');

        $order
            ->expiresAt($this->expires_at->addDays(5));

        return $order;
    }

    /**
     * Returns the field to store when the object was shipped.
     */
    public function getCompletedAtField(): string
    {
        return 'shipped_at';
    }
}
