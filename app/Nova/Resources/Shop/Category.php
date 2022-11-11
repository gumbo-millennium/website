<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Models\Shop\Category as Model;
use App\Nova\Resources\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields;

class Category extends Resource
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
            Fields\ID::make(),

            Fields\Text::make(__('Name'), 'name'),
            Fields\Slug::make(__('Slug'), 'slug')
                ->from('name')
                ->showUrlPreview(url('/shop/'))
                ->hideFromIndex()
                ->nullable(),

            Fields\Boolean::make(__('Visible'), 'visible'),

            Fields\HasMany::make(__('Products'), 'products', Product::class),
        ];
    }
}
