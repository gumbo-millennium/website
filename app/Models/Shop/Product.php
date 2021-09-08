<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Helpers\Arr;
use App\Models\Traits\IsSluggable;
use App\Models\Traits\IsUuidModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
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
 * @property array $features
 * @property-read null|\App\Models\Shop\Category $category
 * @property-read null|\App\Models\Shop\ProductVariant $default_variant
 * @property-read null|\Illuminate\Support\HtmlString $description_html
 * @property-read Collection $detail_feature_icons
 * @property-read Collection $feature_icons
 * @property-read Collection $feature_warnings
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
        // Visibility
        'visible' => 'bool',
        'advertise_on_home' => 'bool',

        // Tax rate (not really used)
        'vat_rate' => 'int',

        // Random metadata
        'meta' => 'json',

        // Features
        'features' => 'json',
    ];

    protected $attributes = [
        'meta' => '[]',
        'features' => '[]',
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

    public function getFeatureIconsAttribute(): Collection
    {
        return $this->getEnrichedFeatures()
            ->mapWithKeys(fn ($feature) => [$feature['icon'] => $feature['title']]);
    }

    public function getDetailFeatureIconsAttribute(): Collection
    {
        return $this->getEnrichedFeatures()
            ->reject(fn ($row) => Arr::has($row, 'notice.text'))
            ->mapWithKeys(fn ($feature) => [$feature['icon'] => $feature['title']]);
    }

    public function getFeatureWarningsAttribute(): Collection
    {
        return $this->getEnrichedFeatures()
            ->filter(fn ($row) => Arr::has($row, 'notice.text'));
    }

    private function getEnrichedFeatures(): Collection
    {
        return collect($this->features)
            ->map(fn ($value, $key) => $value ? $key : null)
            ->filter()
            ->map(fn ($feature) => Config::get("gumbo.shop.features.{$feature}", null))
            ->filter();
    }
}
