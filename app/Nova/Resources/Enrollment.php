<?php

namespace App\Nova\Resources;

use App\Models\Enrollment as EnrollmentModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Text;

class Enrollment extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = EnrollmentModel::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * Name of the group
     *
     * @var string
     */
    public static $group = 'Activities';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Enrollments');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Enrollment');
    }

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

            // Add multi selects
            BelongsTo::make('Activity', 'activity'),
            BelongsTo::make('User', 'user'),

            // Add data
            KeyValue::make(__('Enrollment Data'), 'data')
                ->rules('json')
                ->hideFromIndex(),

            // Dates
            DateTime::make('Created at', 'created_at')
                ->onlyOnDetail(),
            DateTime::make('Updated at', 'updated_at')
                ->onlyOnDetail(),
            DateTime::make('Trashed at', 'deleted_at')
                ->onlyOnDetail(),

            // Add payments
            HasMany::make(__('Payments'), 'payments', Payment::class),
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
