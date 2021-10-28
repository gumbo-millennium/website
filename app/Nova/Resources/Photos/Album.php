<?php

declare(strict_types=1);

namespace App\Nova\Resources\Photos;

use App\Enums\AlbumVisibility;
use App\Models\Photos\Album as AlbumModel;
use App\Nova\Resources\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields;

class Album extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = AlbumModel::class;

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Photo albums';

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
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Fields\ID::make()->sortable(),

            Fields\Text::make('Name')
                ->sortable()
                ->rules([
                    'required', 'max:255',
                ]),

            Fields\Textarea::make('Description')
                ->nullable()
                ->rules([
                    'max:255',
                ]),

            Fields\Select::make('Visibility')
                ->options([
                    AlbumVisibility::HIDDEN => __('Hidden'),
                    AlbumVisibility::MEMBERS_ONLY => __('Members Only'),
                    AlbumVisibility::USERS => __('Users Only'),
                    AlbumVisibility::WORLD => __('Public'),
                ]),

            Fields\HasMany::make('Photos'),

            Fields\BelongsTo::make('User')
                ->onlyOnDetail(),
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
