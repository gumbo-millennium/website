<?php

declare(strict_types=1);

namespace App\Nova\Resources\Gallery;

use App\Models\Gallery\Photo as PhotoModel;
use App\Nova\Resources\Resource;
use App\Nova\Resources\User;
use Illuminate\Http\Request;
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
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name',
        'description',
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Photo Galleries';

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Fields\ID::make(__('ID'), 'id')->sortable(),

            Fields\Text::make(__('Name'), 'name')
                ->sortable()
                ->exceptOnForms(),

            Fields\Text::make(__('Description'), 'description')
                ->hideFromIndex()
                ->nullable()
                ->rules([
                    'nullable',
                    'string',
                    'max:255',
                ]),

            Fields\Image::make(__('Foto'), 'path')
                ->disk('cloud')
                ->disableDownload()
                ->showOnUpdating(false)
                ->storeOriginalName('name'),

            Fields\BelongsTo::make(__('Album'), 'album', Album::class)
                ->rules([
                    'required',
                ]),

            Fields\BelongsTo::make(__('User'), 'user', User::class)
                ->rules([
                    'required',
                ]),

            Fields\DateTime::make(__('Created At'), 'created_at')
                ->onlyOnDetail(),

            Fields\DateTime::make(__('Updated At'), 'updated_at')
                ->onlyOnDetail(),

            Fields\Date::make(__('Taken At'), 'taken_at')
                ->hideFromIndex()
                ->nullable()
                ->rules([
                    'nullable',
                    'date',
                    'before:now',
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
