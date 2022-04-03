<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Models\Enrollment as EnrollmentModel;
use App\Models\Ticket as TicketModel;
use App\Models\User as UserModel;
use App\Nova\Fields\Price;
use Illuminate\Http\Request;
use Laravel\Nova\Fields;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Tickets for enrollments and tickets.
 */
class Ticket extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = TicketModel::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * Name of the group.
     *
     * @var string
     */
    public static $group = 'Activiteiten';

    /**
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = false;

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

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
            Fields\ID::make(),

            Fields\Text::make(__('Title'), 'title')
                ->rules([
                    'required',
                    'max:250',
                ])
                ->sortable(),

            Fields\Textarea::make(__('Description'), 'description')
                ->shouldShow(fn () => true)
                ->nullable()
                ->hideFromIndex(),

            // Add multi selects
            Fields\BelongsTo::make(__('Activity'), 'activity', Activity::class)
                ->exceptOnForms(),

            Fields\Boolean::make(__('Public'), 'is_public'),

            // Availability
            Fields\Heading::make(__('Availability')),

            Fields\DateTime::make(__('Available From'), 'available_from')
                ->rules('nullable', 'date')
                ->hideFromIndex()
                ->nullable()
                ->firstDayOfWeek(1),

            Fields\DateTime::make(__('Available Until'), 'available_until')
                ->rules('nullable', 'date')
                ->hideFromIndex()
                ->nullable()
                ->firstDayOfWeek(1),

            // Pricing
            Fields\Heading::make(__('Pricing and quantity')),

            Price::make(__('Price'), 'price')
                ->hideWhenUpdating()
                ->hideFromIndex()
                ->nullable()
                ->rules('nullable', 'gt:0')
                ->help('Prijs in Euro'),

            Price::make(__('Total price'), 'total_price')
                ->exceptOnForms()
                ->help('Prijs in Euro, incl. transactiekosten'),

            Fields\Number::make(__('Max Quantity'), 'quantity')
                ->rules([
                    'nullable',
                    'integer',
                    'gt:0',
                ]),
        ];
    }
}
