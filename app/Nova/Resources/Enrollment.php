<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Models\Enrollment as EnrollmentModel;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\Paid;
use App\Nova\Actions\UnenrollUser;
use App\Nova\Fields\Price;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * User enrollment
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Enrollment extends Resource
{
    /**
     * The model the resource corresponds to.
     * @var string
     */
    public static $model = EnrollmentModel::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     * @var string
     */
    public static $title = 'id';

    /**
     * Name of the group
     * @var string
     */
    public static $group = 'Activiteiten';

    /**
     * The columns that should be searched.
     * @var array
     */
    public static $search = [
    ];

    /**
     * Get the displayable label of the resource.
     * @return string
     */
    public static function label()
    {
        return 'Inschrijvingen';
    }

    /**
     * Get the displayable singular label of the resource.
     * @return string
     */
    public static function singularLabel()
    {
        return 'Inschrijving';
    }

    /**
     * Make sure the user can only see enrollments he/she is allowed to see
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
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

    /**
     * Get the fields displayed by the resource.
     * @return array<mixed>
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->hideFromIndex(),

            // Add multi selects
            BelongsTo::make('Activiteit', 'activity', Activity::class)
                ->rules('required', static function ($activity) use ($request): void {
                    $request->can('manage', $activity);
                })
                ->hideWhenUpdating(),

            Text::make('Gebruiker', fn () => $this->user->name)
                ->onlyOnIndex()
                ->showOnDetail(),

            // Add user
            BelongsTo::make('Gebruiker', 'user', User::class)
                ->onlyOnForms()
                ->hideWhenUpdating()
                ->rules('required')
                ->searchable(),

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

            // Pricing
            Price::make('Prijs netto', 'price')
                ->onlyOnDetail()
                ->showOnCreating()
                ->rules('nullable', 'gt:0')
                ->help('Prijs in euro, excl. transactiekosten'),

            Price::make('Prijs bruto', 'total_price')
                ->onlyOnIndex()
                ->showOnDetail()
                ->help('Prijs in euro, incl. transactiekosten'),

            Text::make('Status', fn () => $this->state->title)
                ->hideWhenCreating()
                ->hideWhenUpdating(),

            Boolean::make('Betaald', fn () => $this->state instanceof Paid)
                ->onlyOnIndex()
                ->showOnDetail()
                ->help('Geeft aan of de inschrijving is betaald.'),
        ];
    }

    /**
     * Get the actions available on the entity.
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function actions(Request $request)
    {
        return [
            (new UnenrollUser())
                ->onlyOnTableRow()
                ->confirmText('Weet je zeker dat je deze inschrijving wilt annuleren')
                ->cancelButtonText('Niet annuleren')
                ->confirmButtonText('Inschrijving annuleren')
                ->canSee(function () {
                    return !$this->state->isOneOf([Cancelled::class]);
                })
                ->canRun(static function ($request, $enrollment) {
                    if ($enrollment->state->isOneOf([Cancelled::class])) {
                        return false;
                    }
                    $action = $enrollment->price !== null ? 'refund' : 'cancel';
                    return $request->user()->can($action, $enrollment);
                })
        ];
    }
}
