<?php

declare(strict_types=1);

namespace App\Nova\Resources\Gallery;

use App\Models\Gallery\PhotoReport as PhotoReportModel;
use App\Nova\Resources\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields;
use Str;

class PhotoReport extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = PhotoReportModel::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'reason',
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

            Fields\BelongsTo::make(__('Reporter'), 'user', User::class)
                ->onlyOnForms(),

            Fields\Text::make(__('Reporter'), fn () => $this->user?->name)
                ->exceptOnForms(),

            Fields\BelongsTo::make(__('Subject'), 'photo', Photo::class)
                ->onlyOnForms()
                ->rules([
                    'required',
                ]),

            Fields\Text::make(__('Subject'), fn () => __(':name by :author', [
                'name' => Str::limit($this->photo->name, 20),
                'author' => $this->photo->user?->name ?? __('Unknown'),
            ]))->exceptOnForms(),

            Fields\Textarea::make(__('Reason'), 'reason')
                ->sortable()
                ->rules([
                    'required',
                    'string',
                    'max:200',
                ])
                ->hideFromIndex()
                ->readonly(fn () => $this->exists),
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

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title()
    {
        return (string) __('Report from :reporter about :name by :author', [
            'reporter' => $this->user->name,
            'name' => $this->photo->name,
            'author' => $this->photo->user?->name,
        ]);
    }
}
