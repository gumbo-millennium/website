<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Helpers\Str;
use App\Models\Traits\IsSluggable;
use App\Models\Traits\IsUuidModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\HtmlString;

/**
 * A variant of the product, like size or color.
 *
 * @property string $id
 * @property string $product_id
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property string $name
 * @property null|string $description
 * @property string $slug
 * @property int $order
 * @property null|string $image_url
 * @property null|string $sku
 * @property null|int $price
 * @property null|int $order_limit
 * @property array $options
 * @property array $meta
 * @property-read int $applied_order_limit
 * @property-read null|\Illuminate\Support\HtmlString $description_html
 * @property-read string $display_name
 * @property-read string $url
 * @property-read string $valid_image_url
 * @property-read \App\Models\Shop\Order[]|\Illuminate\Database\Eloquent\Collection $orders
 * @property-read \App\Models\Shop\Product $product
 * @method static Builder|ProductVariant findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static Builder|ProductVariant newModelQuery()
 * @method static Builder|ProductVariant newQuery()
 * @method static Builder|ProductVariant query()
 * @method static Builder|ProductVariant whereSlug(string $slug)
 * @method static Builder|ProductVariant withUniqueSlugConstraints(\self $model, $attribute, $config, $slug)
 * @mixin \Eloquent
 */
class ProductVariant extends Model
{
    use IsSluggable;
    use IsUuidModel;

    protected const ORDER_KEYS = [
        '4xs',
        '3xs',
        'xxxs',
        'xxs',
        'xs',
        's',
        'm',
        'l',
        'xl',
        'xxl',
        'xxxl',
        '3xl',
        '4xl',
    ];

    protected $table = 'shop_product_variants';

    protected $casts = [
        // Max number of items per order
        'order_limit' => 'int',

        // Options and metadata
        'options' => 'json',
        'meta' => 'json',
    ];

    protected $attributes = [
        'options' => '[]',
        'meta' => '[]',
    ];

    protected $fillable = [
        'price',
    ];

    public static function boot(): void
    {
        parent::boot();

        self::saving(function (self $variant) {
            if (! $variant->exists || $variant->wasChanged('name') || ! $variant->order) {
                $arrPos = array_search(Str::lower($variant->name), self::ORDER_KEYS, true);

                // order is a TINYINT, max value 255 (1 byte)
                $variant->order = $arrPos !== false ? ($arrPos + 1) : 255;
            }
        });
    }

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

    public function getDisplayNameAttribute(): string
    {
        return $this->product->variants()->count() === 1
            ? $this->product->name
            : ($this->product->name . ' ' . $this->name);
    }

    public function getUrlAttribute(): string
    {
        return route('shop.product-variant', [
            'product' => $this->product,
            'variant' => $this,
        ]);
    }

    public function getDescriptionHtmlAttribute(): ?HtmlString
    {
        if (! $this->description) {
            return null;
        }

        return new HtmlString(nl2br(e(strip_tags($this->description))));
    }

    public function getAppliedOrderLimitAttribute(): int
    {
        if ($this->order_limit > 0) {
            return $this->order_limit;
        }

        return $this->product->applied_order_limit;
    }
}
