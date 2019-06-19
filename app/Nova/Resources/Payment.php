<?php

namespace App\Nova\Resources;

use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Panel;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Number;

class Payment extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Payment::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'provider_id',
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
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

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
                    ->readonly(),

                Number::make(__('Amount Paid'), 'amount')
                    ->readonly()
                    ->help('Refund amount, in Eurocents'),

                KeyValue::make(__('Data'), 'data')
                    ->readonly()
                    ->onlyOnDetail(),
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
                    ->help('Refund amount, in Eurocents'),
            ])
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
