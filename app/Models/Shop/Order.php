<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{

    protected $table = 'shop_orders';

    protected $casts = [
        'price' => 'int',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'shop_order_product_variant')
            ->using(OrderProduct::class);
    }

    public function getStatusAttribute(): string
    {
        if ($this->shipped_at) {
            return 'sent';
        }

         return $this->paid_at ? 'paid' : 'pending';
    }
}
