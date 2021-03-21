<?php

declare(strict_types=1);

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * A product in an order, with a fixed price and tax rate
 *
 * @property string $product_variant_id
 * @property int $order_id
 * @property int $price
 * @property int $vat_rate
 * @property-read \App\Models\Shop\Order $order
 * @property-read \App\Models\Shop\ProductVariant $product
 */
class OrderProduct extends Pivot
{
    protected $table = 'shop_order_product_variant';

    protected $casts = [
        'price' => 'int',
        'vat_rate' => 'int',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
