<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Models\UuidModel;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends UuidModel
{
    use Sluggable;
    use SluggableScopeHelpers;

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
        return $this->hasMany(ProductVariant::class);
    }
}
