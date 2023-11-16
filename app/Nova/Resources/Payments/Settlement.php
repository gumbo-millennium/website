<?php

declare(strict_types=1);

namespace App\Nova\Resources\Payments;

use App\Nova\Resources\Payment;
use App\Nova\Resources\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Nova\Fields;

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

            Fields\DateTime::make('Datum aangemaakt', 'created_at')
                ->sortable(),

            Fields\DateTime::make('Datum uitbetaald', 'settled_at')
                ->sortable(),

            Fields\Status::make('Status', 'status')
                ->loadingWhen(['open', 'pending'])
                ->failedWhen(['failed'])
                ->filterable(fn ($request, $query, $value, $attrbiute) => $query->whereIn('status', Arr::wrap($value))),

            Fields\BelongsToMany::make('Betalingen', 'payments', Payment::class),

            Fields\BelongsToMany::make('Terugbetalingen', 'refunds', Payment::class),
        ];
    }
}
