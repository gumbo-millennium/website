<?php

declare(strict_types=1);

namespace App\Nova\Resources\Gallery;

use App\Models\Gallery\Album as AlbumModel;
use App\Nova\Resources\Activity;
use App\Nova\Resources\Resource;
use App\Nova\Resources\User;
use Illuminate\Http\Request;
use Laravel\Nova\Fields;
use Laravel\Nova\Panel;

class Album extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = AlbumModel::class;

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
                ->rules([
                    'required',
                ]),

            Fields\Slug::make(__('Slug'), 'slug')
                ->sortable()
                ->from('name')
                ->rules([
                    'required',
                    'unique:albums,slug',
                ]),

            Fields\Textarea::make(__('Description'), 'description')
                ->hideFromIndex()
                ->nullable()
                ->rules([
                    'nullable',
                    'string',
                    'max:250',
                ]),

            new Panel(__('Association'), [
                Fields\Text::make(__('Association'), function () {
                    if ($this->user) {
                        return __('Owned by :user (user)', [
                            'user' => $this->user->name,
                        ]);
                    }

                    return __('Attached to :activity (activity)', [
                        'activity' => $this->activity->name,
                    ]);
                })->onlyOnIndex(),

                Fields\BelongsTo::make(__('User'), 'user', User::class)
                    ->readonly(fn () => $this->exists)
                    ->hideFromIndex()
                    ->nullable()
                    ->rules([
                        'nullable',
                        'required_without:activity',
                    ]),

                Fields\BelongsTo::make(__('Activity'), 'activity', Activity::class)
                    ->readonly(fn () => $this->exists)
                    ->hideFromIndex()
                    ->nullable()
                    ->rules([
                        'nullable',
                        'required_without:user',
                    ]),
            ]),

            new Panel(__('Availability'), [
                Fields\Boolean::make(__('Public'), 'public')
                    ->hideFromIndex(),

                Fields\DateTime::make(__('Editable from'), 'editable_from')
                    ->hideFromIndex()
                    ->nullable()
                    ->rules([
                        'nullable',
                        'after:editable_until',
                        'required_with:editable_until',
                    ]),

                Fields\DateTime::make(__('Editable until'), 'editable_until')
                    ->hideFromIndex()
                    ->nullable()
                    ->rules([
                        'nullable',
                        'after:editable_until',
                        'required_with:editable_until',
                    ]),
            ]),

            Fields\HasMany::make(__('Photos'), 'photos', Photo::class),

            Fields\HasMany::make(__('Reports'), 'reports', PhotoReport::class),

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
