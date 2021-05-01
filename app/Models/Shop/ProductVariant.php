<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

namespace App\Models\Shop;

use App\Models\Traits\IsSluggable;
use App\Models\Traits\IsUuidModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A variant of the product, like size or color.
 *
 * @property string $id
 * @property string $product_id
 * @property \Illuminate\Support\Date $created_at
 * @property \Illuminate\Support\Date $updated_at
 * @property string $name
 * @property string|null $description
 * @property string $slug
 * @property string|null $image_url
 * @property string|null $sku
 * @property int|null $price
 * @property array $options
 * @property array $meta
 * @property-read string $valid_image_url
 * @property-read \App\Models\Shop\Product $product
 * @property-read \Illuminate\Database\Eloquent\Collection<Order> $orders
 */
class ProductVariant extends Model
{
    use IsUuidModel;
    use IsSluggable;

    protected $table = 'shop_product_variants';

    protected $casts = [
        'options' => 'json',
        'meta' => 'json',
    ];

    protected $attributes = [
        'options' => '[]',
        'meta' => '[]',
    ];

    /**
     * {@inheritdoc}
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'unique' => true,
                'maxLengthKeepWords' => 48,
                'source' => 'name',
            ],
        ];
    }

    public function scopeWithUniqueSlugConstraints(
        Builder $query,
        self $model,
        $attribute,
        $config,
        $slug
    ) {
        return $query->where('product_id', $model->product_id);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'shop_order_product_variant')
            ->using(OrderProduct::class);
    }

    public function getValidImageUrlAttribute(): string
    {
        return $this->image_url ?? $this->product->valid_image_url;
    }
}
