<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Nova\Actions;
use App\Nova\Fields\Price;
use App\Services\Payments\MolliePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Laravel\Nova\Fields;

class Payment extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Payment';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'description';

    /**
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = false;

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = [
        'payable',
        'user',
    ];

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'transaction_id',
    ];

    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function fields(Request $request)
    {
        return [
            Fields\ID::make()->sortable(),

            Fields\Text::make(__('Subject'), fn () => $this->payable->name ?? $this->payable->title)
                ->exceptOnForms(),

            Fields\Text::make(__('User'), fn () => $this->user?->name)
                ->exceptOnForms(),

            Fields\MorphTo::make(__('Subject'), 'payable')
                ->exceptOnForms(),

            Fields\Text::make(__('Payment service'), 'provider')
                ->onlyOnDetail(),

            Fields\Text::make(__('Transaction ID'), 'transaction_id')
                ->onlyOnDetail(),

            Fields\Text::make('Mollie link', function () {
                if (! $this->transaction_id) {
                    return null;
                }

                return sprintf(
                    '<a href="%s" class="no-underline font-bold dim text-primary" target="_blank">%s</a>',
                    URL::route('admin.mollie.show', $this->id),
                    __('View order in Mollie'),
                );
            })
                ->onlyOnDetail()
                ->showOnDetail(fn () => $this->provider === MolliePaymentService::getName())
                ->asHtml(),

            Price::make(__('Price'), 'price'),
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
            Actions\UpdatePaymentAction::make()
                ->canRun(fn () => $request->user()->can('view', [$this->resource]))
                ->withoutConfirmation(),
        ];
    }
}
