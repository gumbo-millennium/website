<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Models\Traits\IsSluggable;
use App\Models\Traits\IsUuidModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\HtmlString;

/**
 * A single product.
 *
 * @property string $id
 * @property null|string $category_id
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property string $name
 * @property null|string $description
 * @property string $slug
 * @property null|string $image_url
 * @property null|string $etag
 * @property int $vat_rate
 * @property bool $visible
 * @property bool $advertise_on_home
 * @property array $meta
 * @property-read null|\App\Models\Shop\Category $category
 * @property-read null|\App\Models\Shop\ProductVariant $default_variant
 * @property-read null|\Illuminate\Support\HtmlString $description_html
 * @property-read string $valid_image_url
 * @property-read \App\Models\Shop\ProductVariant[]|\Illuminate\Database\Eloquent\Collection $variants
 * @method static \Illuminate\Database\Eloquent\Builder|Product findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereSlug(string $slug)
 * @mixin \Eloquent
 */
class Product extends Model
{
    use IsSluggable;
    use IsUuidModel;

    protected $table = 'shop_products';

    protected $casts = [
        'visible' => 'bool',
        'advertise_on_home' => 'bool',
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
            ->orderBy('order')
            ->orderBy('name');
    }

    public function getValidImageUrlAttribute(): string
    {
        return $this->image_url ?? (string) mix('images/geen-foto.jpg');
    }

    public function getDefaultVariantAttribute(): ?ProductVariant
    {
        return $this->variants
            ->first();
    }

    public function getDescriptionHtmlAttribute(): ?HtmlString
    {
        if (! $this->description) {
            return null;
        }

        return new HtmlString(nl2br(e(strip_tags($this->description))));
    }
}
