<?php

namespace App\Nova\Resources;

use App\Models\Enrollment as EnrollmentModel;
use App\Policies\ActivityPolicy;
use App\Policies\EnrollmentPolicy;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

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
            BelongsTo::make('Activity', 'activity')
                ->rules('required', function ($activity) use ($request) {
                    $request->can('manage', $activity);
                })
                ->hideWhenUpdating(),

            // Add user
            BelongsTo::make('User', 'user')
                ->rules('required')
                ->searchable()
                ->hideWhenUpdating(),

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
            Text::make('Trashed reason', 'deleted_reason')
                ->onlyOnDetail(),

            Boolean::make('Paid', 'paid')
                ->hideWhenUpdating()
                ->help('Indicates if the user has paid the fee for this activity.'),

            // Add payments
            HasMany::make(__('Payments'), 'payments', Payment::class),
        ];
    }

    /**
     * Make sure the user can only see enrollments he/she is allowed to see
     *
     * @param NovaRequest $request
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        // Get user shorthand
        $user = $request->user();

        // Return all enrollments if the user can manage them
        if (EnrollmentPolicy::hasEnrollmentPermissions($user)) {
            return parent::indexQuery($request, $query);
        }

        // Only return enrollments of the user's events if the user is not
        // allowed to globally manage events.
        return parent::indexQuery(
            $request,
            $query->whereIn('id', ActivityPolicy::getAllActivityIds($user))
        );
    }
}
