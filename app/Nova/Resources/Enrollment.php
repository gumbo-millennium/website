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

/**
 * User enrollment
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
    public static $group = 'Activiteiten';

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
        return 'Inschrijvingen';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Inschrijving';
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
            BelongsTo::make('Activiteit', 'activity', Activity::class)
                ->rules('required', function ($activity) use ($request) {
                    $request->can('manage', $activity);
                })
                ->hideWhenUpdating(),

            // Add user
            BelongsTo::make('Gebruiker', 'user', User::class)
                ->showOnIndex()
                ->rules('required')
                ->searchable()
                ->hideWhenUpdating(),

            // Add data
            KeyValue::make('Metadata inschrijving', 'data')
                ->rules('json')
                ->hideFromIndex(),

            // Dates
            DateTime::make('Aangemaakt op', 'created_at')
                ->onlyOnDetail(),
            DateTime::make('Laatst bewerkt op', 'updated_at')
                ->onlyOnDetail(),
            DateTime::make('Verwijderd op', 'deleted_at')
                ->onlyOnDetail(),
            Text::make('Reden verwijdering', 'deleted_reason')
                ->onlyOnDetail(),

            Boolean::make('Betaald', 'paid')
                ->hideWhenUpdating()
                ->help('Geeft aan of de inschrijving is betaald.'),
        ];
    }

    /**
     * Make sure the user can only see enrollments he/she is allowed to see
     *
     * @param NovaRequest $request
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     * @var App\Models\User $user
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        // Get user shorthand
        $user = $request->user();

        // Return all enrollments if the user can manage them
        if ($user->can('admin', EnrollmentModel::class)) {
            return parent::indexQuery($request, $query);
        }

        // Only return enrollments of the user's events if the user is not
        // allowed to globally manage events.
        return parent::indexQuery(
            $request,
            $query->whereIn('activity_id', $user->hosted_activity_ids)
        );
    }
}
