<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Models\Shop\Product as Model;
use App\Nova\Resources\Resource;
use Benjaminhirsch\NovaSlugField\Slug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\BooleanGroup;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class Product extends Resource
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
        $featuresMap = collect(Config::get('gumbo.shop.features', []))
            ->mapWithKeys(fn ($row, $key) => [$key => $row['title']])
            ->all();

        return [
            ID::make(),

            Text::make(__('Name'), 'name'),
            Slug::make(__('Slug'), 'slug')
                ->disableAutoUpdateWhenUpdating()
                ->showUrlPreview(url('/shop/<category>/'))
                ->hideFromIndex()
                ->nullable(),

            Textarea::make(__('Description'), 'description')
                ->hideFromIndex()
                ->nullable()
                ->rows(5)
                ->rules([
                    'nullable',
                    'max:65536',
                ]),

            Text::make(__('Image URL'), 'image_url')
                ->hideFromIndex()
                ->nullable(),

            Text::make(__('Entity Tag'), 'etag')
                ->onlyOnDetail(),

            Number::make(__('VAT'), 'vat_rate')
                ->onlyOnDetail()
                ->min(0)
                ->max(100),

            Number::make(__('Order limit'), 'order_limit')
                ->nullable()
                ->min(1)
                ->max(255)
                ->help(implode(' ', [
                    __('The max count of this variant that can be added to a single order.'),
                    __('Can be overruled on the variant level.'),
                ])),

            BooleanGroup::make(__('Features'), 'features')
                ->options($featuresMap)
                ->help(__('Additional properties to add to (variants of) this product.')),

            Boolean::make(__('Visible'), 'visible'),

            Boolean::make(__('Advertise on homepage and shop landing'), 'advertise_on_home')
                ->help(__('The category and the product need to be visible for the advertisement to show.')),

            BelongsTo::make(__('Category'), 'category', Category::class)
                ->searchable(),

            HasMany::make(__('Variants'), 'variants', ProductVariant::class),
        ];
    }
}
