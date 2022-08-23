<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Models\Enrollment as EnrollmentModel;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\Paid;
use App\Models\User as UserModel;
use App\Nova\Actions\CancelEnrollment;
use App\Nova\Actions\ConfirmEnrollment;
use App\Nova\Actions\TransferEnrollment;
use App\Nova\Fields\Price;
use App\Nova\Filters\EnrollmentStateFilter;
use App\Nova\Filters\PaymentStatusFilter;
use App\Nova\Metrics\ConfirmedEnrollments;
use App\Nova\Metrics\NewEnrollments;
use App\Nova\Metrics\PendingEnrollments;
use Illuminate\Http\Request;
use Laravel\Nova\Fields;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * User enrollment.
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
     * Name of the group.
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
     * Make sure the user can only see enrollments he/she is allowed to see.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        // Get user shorthand
        $user = $request->user();
        \assert($user instanceof UserModel);

        // Return all enrollments if the user can manage them
        if ($user->can('admin', EnrollmentModel::class)) {
            return parent::indexQuery($request, $query);
        }

        // Only return enrollments of the user's events if the user is not
        // allowed to globally manage events.
        return parent::indexQuery($request, $query->whereIn(
            'activity_id',
            $user->getHostedActivityIdQuery(),
        ));
    }

    /**
     * Build a "relatable" query for the given resource.
     *
     * This query determines which instances of the model may be attached to other resources.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function relatableQuery(NovaRequest $request, $query)
    {
        // Get user shorthand
        $user = $request->user();
        \assert($user instanceof UserModel);

        // Return all enrollments if the user can manage them
        if ($user->can('admin', EnrollmentModel::class)) {
            return parent::relatableQuery($request, $query);
        }

        // Only return enrollments of the user's events if the user is not
        // allowed to globally manage events.
        return parent::relatableQuery($request, $query->whereIn(
            'activity_id',
            $user->getHostedActivityIdQuery(),
        ));
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<mixed>
     */
    public function fields(Request $request)
    {
        return [
            Fields\ID::make()->hideFromIndex(),

            // Add multi selects
            Fields\BelongsTo::make('Activiteit', 'activity', Activity::class)
                ->rules('required', static function ($activity) use ($request): void {
                    $request->can('manage', $activity);
                })
                ->hideWhenUpdating(),

            Fields\Text::make('Gebruiker', fn () => $this->user?->name)
                ->onlyOnIndex()
                ->showOnDetail(),

            // Add user
            Fields\BelongsTo::make('Gebruiker', 'user', User::class)
                ->onlyOnForms()
                ->hideWhenUpdating()
                ->rules('required')
                ->searchable(),

            // Add data
            Fields\KeyValue::make('Metadata inschrijving', 'form')
                ->keyLabel(__('Field'))
                ->valueLabel(__('Value'))
                ->onlyOnDetail(),

            // Add ticket type
            Fields\BelongsTo::make(__('Ticket'), 'ticket', Ticket::class)
                ->hideWhenUpdating(),

            // Dates
            Fields\DateTime::make('Aangemaakt op', 'created_at')
                ->onlyOnDetail(),
            Fields\DateTime::make('Laatst bewerkt op', 'updated_at')
                ->onlyOnDetail(),
            Fields\DateTime::make('Verwijderd op', 'deleted_at')
                ->onlyOnDetail(),
            Fields\Text::make('Reden verwijdering', 'deleted_reason')
                ->onlyOnDetail(),
            Fields\DateTime::make('Afloopdatum', 'expire')
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

            Fields\Text::make('Status', fn () => $this->state->title)
                ->hideWhenCreating()
                ->hideWhenUpdating(),

            Fields\Boolean::make('Betaald', fn () => $this->state instanceof Paid)
                ->onlyOnIndex()
                ->showOnDetail()
                ->help('Geeft aan of de inschrijving is betaald.'),
        ];
    }

    /**
     * Get the actions available on the entity.
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            (new CancelEnrollment())
                ->onlyOnTableRow()
                ->confirmText('Weet je zeker dat je deze inschrijving wilt annuleren')
                ->cancelButtonText('Niet annuleren')
                ->confirmButtonText('Inschrijving annuleren')
                ->canSee(fn () => ! $this->state->isOneOf([Cancelled::class]))
                ->canRun(static function ($request, $enrollment) {
                    if ($enrollment->state->isOneOf([Cancelled::class])) {
                        return false;
                    }
                    $action = $enrollment->price !== null ? 'refund' : 'cancel';

                    return $request->user()->can($action, $enrollment);
                }),
            (new TransferEnrollment())
                ->onlyOnTableRow()
                ->confirmText('Weet je zeker dat je deze inschrijving wil overschrijven naar een andere gebruiker?')
                ->cancelButtonText('Annuleren')
                ->confirmButtonText('Inschrijving overschrijven')
                ->canSee(fn () => ! $this->state->isOneOf([Cancelled::class]))
                ->canRun(static function ($request, $enrollment) {
                    if ($enrollment->state->isOneOf([Cancelled::class])) {
                        return false;
                    }

                    return $request->user()->can('manage', $enrollment);
                }),
            ConfirmEnrollment::make($this->model(), $request->user()),
        ];
    }

    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function cards(Request $request)
    {
        return [
            new NewEnrollments(),
            new PendingEnrollments(),
            new ConfirmedEnrollments(),
        ];
    }

    /**
     * Get the filters available on the entity.
     *
     * @return array
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function filters(Request $request)
    {
        return [
            new EnrollmentStateFilter(),
            new PaymentStatusFilter(),
        ];
    }
}
