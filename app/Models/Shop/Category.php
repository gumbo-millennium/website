<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Models\Traits\IsSluggable;
use App\Models\Traits\IsUuidModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A shop category.
 *
 * @property string $id
 * @property \Illuminate\Support\Date $created_at
 * @property \Illuminate\Support\Date $updated_at
 * @property string $name
 * @property string|null $description
 * @property string $slug
 * @property bool $visible
 * @property array $meta
 * @property-read string $valid_image_url
 * @property-read \Illuminate\Database\Eloquent\Collection<Product> $products
 */
class Category extends Model
{
    use IsUuidModel;
    use IsSluggable;

    protected $table = 'shop_categories';

    protected $casts = [
        'visible' => 'bool',
        'meta' => 'json',
    ];

    protected $attributes = [
        'meta' => '[]',
        'visible' => 0,
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

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }


    public function getValidImageUrlAttribute(): string
    {
        return $this->products->whereNotNull('image_url')->where('active', '=', 1)->first()->image_url
            ?? (string) mix('images/geen-foto.jpg');
    }
}
