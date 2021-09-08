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
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property string $name
 * @property null|string $description
 * @property string $slug
 * @property bool $visible
 * @property array $meta
 * @property-read string $valid_image_url
 * @property-read \App\Models\Shop\Product[]|\Illuminate\Database\Eloquent\Collection $products
 * @method static \Illuminate\Database\Eloquent\Builder|Category findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereSlug(string $slug)
 * @mixin \Eloquent
 */
class Category extends Model
{
    use IsSluggable;
    use IsUuidModel;

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
                'reserved' => [
                    'item',
                    'winkelwagen',
                    'plaats-bestelling',
                    'bestellingen',
                ],
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
