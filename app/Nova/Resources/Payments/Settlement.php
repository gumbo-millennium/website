<?php

declare(strict_types=1);

namespace App\Nova\Resources\Payments;

use App\Nova\Actions\Payments\UpdateSettlement;
use App\Nova\Fields\Price;
use App\Nova\Resources\Payment;
use App\Nova\Resources\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields;
use Laravel\Nova\Http\Requests\NovaRequest;

class Settlement extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Payments\Settlement';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'reference';

    /**
     * Name of the group.
     *
     * @var string
     */
    public static $group = 'Bestuurszaken';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'mollie_id',
        'reference',
    ];

    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function fields(Request $request)
    {
        return [
            Fields\ID::make()->sortable(),

            Fields\Text::make('Referentie', 'reference'),

            Price::make('Bedrag', 'amount')
                ->nullable(),

            Price::make('Mollie kosten', 'fees')
                ->onlyOnDetail()
                ->nullable(),

            Fields\Date::make('Datum aangemaakt', 'created_at')
                ->sortable(),

            Fields\Date::make('Datum uitbetaald', 'settled_at')
                ->sortable(),

            Fields\Status::make('Status', 'status')
                ->loadingWhen(['open', 'pending'])
                ->failedWhen(['failed'])
                ->filterable(fn ($request, $query, $value, $attrbiute) => $query->whereIn('status', Arr::wrap($value))),

            Fields\BelongsToMany::make('Betalingen', 'payments', Payment::class),

            Fields\BelongsToMany::make('Terugbetalingen', 'refunds', Payment::class),
        ];
    }

    /**
     * Get the actions available on the entity.
     *
     * @return array<Action>
     */
    public function actions(NovaRequest $request)
    {
        return static::defaultsWith([
            UpdateSettlement::make()
                ->showOnDetail()
                ->showOnIndex(),
        ]);
    }
}
