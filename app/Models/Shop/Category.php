<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Fluent\Image;
use App\Models\SluggableModel;
use App\Models\Traits\IsUuidModel;
use Database\Factories\Shop\CategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;

/**
 * App\Models\Shop\Category.
 *
 * @property string $id
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|\Illuminate\Support\Carbon $deleted_at
 * @property string $name
 * @property null|string $description
 * @property string $slug
 * @property bool $visible
 * @property array $meta
 * @property-read null|string $valid_image
 * @property-read string $valid_image_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Shop\Product> $products
 * @method static \Database\Factories\Shop\CategoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|SluggableModel findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static Builder|Category newModelQuery()
 * @method static Builder|Category newQuery()
 * @method static Builder|Category onlyTrashed()
 * @method static Builder|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder|SluggableModel whereSlug(string $slug)
 * @method static Builder|Category withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|SluggableModel withUniqueSlugConstraints(\Illuminate\Database\Eloquent\Model $model, string $attribute, array $config, string $slug)
 * @method static Builder|Category withoutTrashed()
 * @mixin \Eloquent
 */
class Category extends SluggableModel
{
    use HasFactory;
    use IsUuidModel;
    use SoftDeletes;

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
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return CategoryFactory::new();
    }

    /**
     * Returns a sluggable definition for this model.
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

    public function getValidImageAttribute(): ?string
    {
        $productWithImage = $this->products()
            // Only find visible
            ->where('visible', true)

            // Only find products with an image or with
            // a variant with an image.
            ->where(
                fn (Builder $query) => $query
                    ->whereNotNull('image_path')
                    ->orWhereHas('variants', function (Builder $query) {
                        $query->whereNotNull('image_path');
                    }),
            );

        // Also just find the first image
        $firstProduct = $this->products()->where('visible', true);

        // We make the first()-call here, so prevent excessive queries
        return $productWithImage->first()?->valid_image
            ?? $firstProduct->first()?->valid_image;
    }

    public function getValidImageUrlAttribute(): string
    {
        return $this->products->whereNotNull('image_url')->where('active', '=', 1)->first()->image_url
            ?? (string) Vite::image('images/geen-foto.jpg');
    }
}
