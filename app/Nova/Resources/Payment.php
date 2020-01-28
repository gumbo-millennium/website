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
        return 'Betalingen';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Betaling';
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
            DateTime::make('Aangemaakt op', 'created_at')
                ->onlyOnDetail(),

            DateTime::make('Laatst bewerkt op', 'updated_at')
                ->onlyOnDetail(),

            DateTime::make('Afgerond op', 'refunded_at')
                ->onlyOnDetail(),

            new Panel('Details', [
                Text::make('Provider', 'provider')
                    ->readonly(),

                Text::make('Provider ID', 'provider_id')
                    ->readonly()
                    ->canSee(function ($request) {
                        return $request->user()->can('admin', $this);
                    }),

                Number::make('Hoeveelheid betaald', 'amount')
                    ->readonly()
                    ->help('Waarde van betaling, in eurocenten'),

                KeyValue::make('Data', 'data')
                    ->readonly()
                    ->onlyOnDetail()
                    ->canSee(function ($request) {
                        return $request->user()->can('admin', $this);
                    }),
            ]),

            new Panel('Terugbetaling', [
                DateTime::make('Terugbetaald op', 'refunded_at')
                    ->onlyOnDetail(),

                Number::make('Hoeveelheid', 'refund_amount')
                    ->readonly()
                    ->help('Waarde van terugbetaling, in eurocenten'),

                Boolean::make('Volledig', 'fully_refunded')
                    ->readonly()
                    ->onlyOnDetail()
                    ->help('Waar indien het gehele bedrag terug is betaald.'),
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
