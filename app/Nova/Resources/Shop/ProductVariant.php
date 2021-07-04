<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Models\Shop\ProductVariant as Model;
use App\Nova\Fields\Price;
use App\Nova\Resources\Resource;
use Benjaminhirsch\NovaSlugField\Slug;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

class ProductVariant extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = Model::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Shop';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'name',
        'slug',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(),

            Text::make(__('Name'), 'name'),
            Slug::make(__('Slug'), 'slug')
                ->disableAutoUpdateWhenUpdating()
                ->hideFromIndex()
                ->nullable(),

            Text::make(__('Description'), 'description')
                ->hideFromIndex()
                ->nullable(),

            Text::make('SKU', 'sku')
                ->hideFromIndex()
                ->nullable(),

            Price::make(__('Price'), 'price')
                ->sortable()
                ->min(1.00),

            BelongsTo::make(__('Product'), 'product', Product::class)
                ->searchable(),

            BelongsToMany::make(__('Orders'), 'orders', Order::class)
                ->fields(new OrderProductFields()),
        ];
    }
}
