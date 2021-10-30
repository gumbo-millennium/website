<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Models\Photo as PhotoModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields;

class Photo extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = PhotoModel::class;

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Photo Albums';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = false;

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name',
        'caption',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Fields\ID::make()->sortable(),

            Fields\BelongsTo::make(__('Album'), 'album', PhotoAlbum::class)
                ->hideWhenUpdating()
                ->rules([
                    'required',
                ]),

            Fields\BelongsTo::make(__('User'), 'user', User::class)
                ->exceptOnForms(),

            Fields\Text::make(__('Caption'), 'caption')
                ->rules([
                    'required',
                    'max:255',
                ]),

            Fields\Image::make(__('Image'), 'path')
                ->disk(Config::get('gumbo.photos.storage-disk'))
                ->hideWhenUpdating()
                ->prunable()
                ->help(__('The highest resolution of the image you got, but at least 300x300.'))
                ->rules([
                    'required',
                    'image',
                    Rule::dimensions()
                        ->minWidth(300)
                        ->minHeight(300),
                ]),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
