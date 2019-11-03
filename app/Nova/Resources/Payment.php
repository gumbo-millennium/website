<?php

namespace App\Nova\Resources;

use App\Models\Payment as PaymentModel;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Panel;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Enrollment payments
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Payment extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = PaymentModel::class;

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
        'transaction_id',
    ];
    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Payments');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Payment');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fields(Request $request)
    {
        return [
            Text::make('ID', 'id')
                ->sortable()
                ->exceptOnForms(),

            // Dates
            DateTime::make(__('Created At'), 'created_at')
                ->onlyOnDetail(),

            DateTime::make(__('Updated At'), 'updated_at')
                ->onlyOnDetail(),

            DateTime::make(__('Completed At'), 'refunded_at')
                ->onlyOnDetail(),

            new Panel(__('Payment Details'), [
                Text::make(__('Provider'), 'provider')
                    ->readonly(),

                Text::make(__('Provider ID'), 'provider_id')
                    ->readonly()
                    ->canSee(function ($request) {
                        return $request->user()->can('admin', $this);
                    }),

                Number::make(__('Amount Paid'), 'amount')
                    ->readonly()
                    ->help('Paid amount, in Eurocents'),

                KeyValue::make(__('Data'), 'data')
                    ->readonly()
                    ->onlyOnDetail()
                    ->canSee(function ($request) {
                        return $request->user()->can('admin', $this);
                    }),
            ]),

            new Panel(__('Refund Information'), [
                DateTime::make(__('Refunded At'), 'refunded_at')
                    ->onlyOnDetail(),

                Number::make(__('Amount Refunded'), 'refund_amount')
                    ->readonly()
                    ->help('Refund amount, in Eurocents'),

                Boolean::make(__('Fully refunded'), 'fully_refunded')
                    ->readonly()
                    ->onlyOnDetail()
                    ->help('True if the entire paid amount was returned to the user'),
            ])
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
        if ($user->can('admin', PaymentModel::class)) {
            return parent::indexQuery($request, $query);
        }

        // Only return enrollments of the user's events if the user is not
        // allowed to globally manage events.
        return parent::indexQuery(
            $request,
            $query->whereIn('enrollment.activity_id', $user->hosted_activity_ids)
        );
    }
}
