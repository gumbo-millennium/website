<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Helpers\Str;
use App\Models\Enrollment as EnrollmentModel;
use App\Models\Ticket as TicketModel;
use App\Models\User as UserModel;
use App\Nova\Fields\Price;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Laravel\Nova\Fields;
use Laravel\Nova\Fields\FormData;
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
    public static $group = 'Activities';

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

            Price::make(__('Price'), 'price')
                ->hideFromIndex()
                ->nullable()
                ->step('0.01')
                ->rules('nullable', 'gt:0')
                ->help(
                    __('Price in euro, excluding :price fees. Leave empty for free tickets.', [
                        'price' => Str::price(Config::get('gumbo.transfer-fee')),
                    ]),
                ),

            Price::make(__('Total price'), 'total_price')
                ->exceptOnForms()
                ->help(__('Price in euro, including fees.')),

            // Availability
            Fields\Heading::make(__('Custom Constraints'))
                ->help(implode("\n", [
                    __("If required, you can __deviate__ from the Activity's set limits (such as enrollment windows and seats)."),
                    __("The limits specified here must be within the Activity's limits."),
                ])),

            Fields\Boolean::make(__('Use custom constraints'), 'custom_availability')
                ->help(__('If enabled, you can set a custom enrollment window and quantity for this ticket.'))
                ->resolveUsing(fn () => (bool) ($this->available_from || $this->available_until || $this->quantity))
                ->fillUsing(fn () => null) // noop
                ->default(false),

            Fields\DateTime::make(__('Available From'), 'available_from')
                ->rules('nullable', 'date')
                ->hideFromIndex()
                ->nullable()
                ->hide()
                ->dependsOn('custom_availability', function (Fields\Field $field, NovaRequest $request, FormData $formData) {
                    if ($formData->boolean('custom_availability') === true) {
                        $field->show();
                    }
                }),

            Fields\DateTime::make(__('Available Until'), 'available_until')
                ->rules('nullable', 'date')
                ->hideFromIndex()
                ->nullable()
                ->hide()
                ->dependsOn('custom_availability', function (Fields\Field $field, NovaRequest $request, FormData $formData) {
                    if ($formData->boolean('custom_availability') === true) {
                        $field->show();
                    }
                }),

            Fields\Number::make(__('Quantity'), 'quantity')
                ->help(__('Note that the number of seats on the activity, if specified, might affect this quantity.'))
                ->rules([
                    'nullable',
                    'integer',
                    'gt:0',
                ])
                ->hideFromIndex()
                ->hide()
                ->dependsOn('custom_availability', function (Fields\Field $field, NovaRequest $request, FormData $formData) {
                    if ($formData->boolean('custom_availability') === true) {
                        $field->show();
                    }
                }),
        ];
    }
}
