<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Fluent\Image;
use App\Helpers\Arr;
use App\Models\Traits\IsSluggable;
use App\Models\Traits\IsUuidModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\HtmlString;

/**
 * App\Models\Shop\Product.
 *
 * @property string $id
 * @property null|string $category_id
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|\Illuminate\Support\Carbon $deleted_at
 * @property string $name
 * @property null|string $description
 * @property string $slug
 * @property null|string $image_path
 * @property null|string $etag
 * @property int $vat_rate
 * @property null|int $order_limit
 * @property bool $visible
 * @property bool $advertise_on_home
 * @property array $meta
 * @property array $features
 * @property-read null|\App\Models\Shop\Category $category
 * @property-read int $applied_order_limit
 * @property-read null|\App\Models\Shop\ProductVariant $default_variant
 * @property-read null|\Illuminate\Support\HtmlString $description_html
 * @property-read Collection $detail_feature_icons
 * @property-read Collection $feature_icons
 * @property-read Collection $feature_warnings
 * @property-read Image $image
 * @property-read null|string $image_url
 * @property-read Image $valid_image
 * @property-read string $valid_image_url
 * @property-read \App\Models\Shop\ProductVariant[]|\Illuminate\Database\Eloquent\Collection $variants
 * @method static \Illuminate\Database\Eloquent\Builder|Product findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Query\Builder|Product onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereSlug(string $slug)
 * @method static \Illuminate\Database\Query\Builder|Product withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Product withoutTrashed()
 * @mixin \Eloquent
 */
class Product extends Model
{
    use IsSluggable;
    use IsUuidModel;
    use SoftDeletes;

    protected $table = 'shop_products';

    protected $casts = [
        // Visibility
        'visible' => 'bool',
        'advertise_on_home' => 'bool',

        // Tax rate (not really used)
        'vat_rate' => 'int',

        // Max number of items per order (applied per variant)
        'order_limit' => 'int',

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
        return $this->valid_image->getUrl();
    }

    public function getValidImageAttribute(): Image
    {
        return $this->image_path ? $this->image : Image::make(url((string) mix('images/geen-foto.jpg')));
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

    public function getAppliedOrderLimitAttribute(): int
    {
        if ($this->order_limit > 0) {
            return $this->order_limit;
        }

        return Config::get('gumbo.shop.order-limit');
    }

    public function getImageAttribute(): Image
    {
        return Image::make($this->image_path);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return $this->image->getUrl();
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
