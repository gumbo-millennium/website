<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Fluent\Image;
use App\Models\Traits\IsSluggable;
use App\Models\Traits\IsUuidModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\URL;

/**
 * A shop category.
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
 * @property-read Image $valid_image
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

    public function getValidImageAttribute(): Image
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
        $firstProduct = $this->products->where('visible', true);

        $fallback = Image::make(URL::to('/images/geen-foto.jpg'));

        // We make the first()-call here, so prevent excessive queries
        return optional($productWithImage->first())->valid_image
            ?? optional($firstProduct->first())->valid_image
            ?? $fallback;
    }

    public function getValidImageUrlAttribute(): string
    {
        return $this->products->whereNotNull('image_url')->where('active', '=', 1)->first()->image_url
            ?? (string) mix('images/geen-foto.jpg');
    }
}
