<?php

declare(strict_types=1);

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\Shop\OrderProduct.
 *
 * @property string $product_variant_id
 * @property int $order_id
 * @property int $price
 * @property int $quantity
 * @property-read null|\App\Models\Shop\Order $order
 * @property-read null|\App\Models\Shop\ProductVariant $variant
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProduct query()
 * @mixin \Eloquent
 */
class OrderProduct extends Pivot
{
    protected $table = 'shop_order_product_variant';

    protected $casts = [
        'price' => 'int',
        'quantity' => 'int',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_id')
            ->withTrashed();
    }
}
