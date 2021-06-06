<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Models\Traits\IsSluggable;
use App\Models\Traits\IsUuidModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A single product.
 *
 * @property string $id
 * @property string|null $category_id
 * @property \Illuminate\Support\Date $created_at
 * @property \Illuminate\Support\Date $updated_at
 * @property string $name
 * @property string|null $description
 * @property string $slug
 * @property string|null $image_url
 * @property string|null $etag
 * @property int $vat_rate
 * @property bool $visible
 * @property array $meta
 * @property-read string $valid_image_url
 * @property-read \App\Models\Shop\Category|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection<ProductVariant> $variants
 */
class Product extends Model
{
    use IsUuidModel;
    use IsSluggable;

    protected $table = 'shop_products';

    protected $casts = [
        'visible' => 'bool',
        'vat_rate' => 'int',
        'meta' => 'json',
    ];

    protected $attributes = [
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)
            ->orderBy('order');
    }

    public function getValidImageUrlAttribute(): string
    {
        return $this->image_url ?? (string) mix('images/geen-foto.jpg');
    }
}
