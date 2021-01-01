<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Models\Traits\IsSluggable;
use App\Models\Traits\IsUuidModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        return $this->hasMany(ProductVariant::class);
    }
}
