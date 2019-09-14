<?php

namespace App\Nova\Resources;

use Advoor\NovaEditorJs\NovaEditorJs;
use App\Models\NewsItem as NewsItemModel;
use Benjaminhirsch\NovaSlugField\Slug;
use Benjaminhirsch\NovaSlugField\TextWithSlug;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;

class NewsItem extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = NewsItemModel::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * Name of the group
     *
     * @var string
     */
    public static $group = 'Content';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'title',
        'slug',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            TextWithSlug::make('Title', 'title')->slug('slug'),
            Slug::make('Slug', 'slug')
                ->nullable(false)
                ->readonly(function () {
                    return array_key_exists($this->slug, NewsItemModel::REQUIRED_PAGES);
                }),

            // Add multi selects
            BelongsTo::make('Last modified by', 'author', User::class)
                ->onlyOnDetail(),

            // Show timestamps
            DateTime::make('Created at', 'created_at')->onlyOnDetail(),
            DateTime::make('Updated at', 'created_at')->onlyOnDetail(),

            // Add data
            NovaEditorJs::make('Contents', 'contents')->hideFromIndex()->stacked(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
