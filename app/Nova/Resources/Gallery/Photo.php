<?php

declare(strict_types=1);

namespace App\Nova\Resources\Gallery;

use App\Enums\AlbumVisibility;
use App\Enums\PhotoVisibility;
use App\Fluent\Image;
use App\Models\Gallery\Photo as PhotoModel;
use App\Nova\Filters\Gallery\PhotoVisibilityFilter;
use App\Nova\Resources\Resource;
use App\Nova\Resources\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Laravel\Nova\Fields;
use Laravel\Nova\Http\Requests\NovaRequest;

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
     * Build an "index" query for the given resource.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        $user = $request->user();

        return parent::indexQuery($request, $query)
            ->where('visibility', '!=', PhotoVisibility::Pending)
            ->whereHas('album', function ($query) use ($user) {
                $query
                    ->where('visibility', AlbumVisibility::Public)
                    ->orWhereHas('user', fn ($query) => $query->where('id', $user->id));
            })
            ->where(function ($query) use ($user) {
                $query
                    ->where('visibility', PhotoVisibility::Visible)
                    ->orWhere(function ($query) use ($user) {
                        $query
                            ->whereHas('user', fn ($query) => $query->whereId($user->id))
                            ->where('visibility', PhotoVisibility::Hidden);
                    });
            });
    }

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
                ->hideWhenUpdating()
                ->help(__('Name of the photo, usually the filename')),

            Fields\Text::make(__('Description'), 'description')
                ->hideFromIndex()
                ->nullable()
                ->rules([
                    'nullable',
                    'string',
                    'max:255',
                ]),

            Fields\Image::make(__('Foto'), 'path')
                ->disk(Config::get('gumbo.images.disk'))
                ->path(Config::get('gumbo.images.path'))
                ->thumbnail(fn () => (string) Image::make($this->path)->preset('nova-thumbnail'))
                ->preview(fn () => (string) Image::make($this->path)->preset('nova-preview'))
                ->disableDownload()
                ->showOnUpdating(false)
                ->storeOriginalName('name'),

            Fields\Select::make(__('Visibility'), 'visibility')
                ->displayUsing(fn (?PhotoVisibility $value) => __(($value ?? PhotoVisibility::Pending)->name))
                ->options(
                    Collection::make(PhotoVisibility::cases())
                        ->mapWithKeys(fn (PhotoVisibility $vis) => [$vis->value => __($vis->name)]),
                ),

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

            Fields\DateTime::make(__('Taken At'), 'taken_at')
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
        return [
            new PhotoVisibilityFilter(),
        ];
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
