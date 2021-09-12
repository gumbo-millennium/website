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
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

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

            Text::make(__('Name'), 'name')
                ->readonly(),

            Slug::make(__('Slug'), 'slug')
                ->disableAutoUpdateWhenUpdating()
                ->onlyOnDetail()
                ->nullable(),

            Textarea::make(__('Description'), 'description')
                ->hideFromIndex()
                ->nullable()
                ->rows(5)
                ->rules([
                    'nullable',
                    'max:65536',
                ]),

            Text::make('SKU', 'sku')
                ->onlyOnDetail()
                ->nullable(),

            Price::make(__('Price'), 'price')
                ->onlyOnDetail()
                ->sortable()
                ->help(__('Price changes need to be entered in Zettle, you cannot update the product price here.')),

            Number::make(__('Order limit'), 'order_limit')
                ->nullable()
                ->min(1)
                ->max(255)
                ->help(__('The max count of this variant that can be added to a single order.')),

            BelongsTo::make(__('Product'), 'product', Product::class)
                ->searchable(),

            BelongsToMany::make(__('Orders'), 'orders', Order::class)
                ->fields(new OrderProductFields()),
        ];
    }
}
