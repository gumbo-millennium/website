<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Contracts\Payments\PayableModel;
use App\Models\Shop\Order as Model;
use App\Nova\Actions\Shop\CancelOrder;
use App\Nova\Actions\Shop\ShipOrder;
use App\Nova\Fields\Price;
use App\Nova\Resources\Resource;
use App\Nova\Resources\User;
use Illuminate\Http\Request;
use Laravel\Nova\Fields;

// phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
class Order extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = Model::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Shop';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'number',
    ];

    /**
     * The relationships that should be eager loaded on index queries.
     *
     * @var array
     */
    public static $with = [
        'user',
        'variants',
    ];


    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Fields\ID::make(__('Order Number'), 'number'),

            Fields\DateTime::make(__('Paid at'), 'paid_at')
                ->onlyOnDetail(),

            Fields\DateTime::make(__('Shipped at'), 'shipped_at')
                ->onlyOnDetail(),

            Fields\DateTime::make(__('Cancelled at'), 'cancelled_at')
                ->onlyOnDetail(),

            Fields\DateTime::make(__('Expires at'), 'expires_at')
                ->onlyOnDetail(),

            Fields\BelongsTo::make(__('User'), 'user', User::class)
                ->exceptOnForms(),

            Fields\Badge::make(__('Status'), 'payment_status')
                ->onlyOnIndex()
                ->displayUsing(static fn ($status) => __("gumbo.payment-status.{$status}"))
                ->map([
                    __('gumbo.payment-status.' . PayableModel::STATUS_UNKNOWN) => 'warning',
                    __('gumbo.payment-status.' . PayableModel::STATUS_OPEN) => 'warning',
                    __('gumbo.payment-status.' . PayableModel::STATUS_PAID) => 'info',
                    __('gumbo.payment-status.' . PayableModel::STATUS_CANCELLED) => 'danger',
                    __('gumbo.payment-status.' . PayableModel::STATUS_COMPLETED) => 'success',
                ]),

            Price::make(__('Price'), 'price')
                ->sortable()
                ->min(1.00),

            Price::make(__('Fees'), 'fee')
                ->sortable()
                ->min(0.00)
                ->hideFromIndex(),

            Fields\Number::make(__('Number of products'), fn () => $this->variants->sum('pivot.quantity'))
                ->onlyOnIndex(),

            Fields\BelongsToMany::make(__('Products'), 'variants', ProductVariant::class)
                ->fields(new OrderProductFields()),
        ];
    }

    /**
     * Get the actions available on the entity.
     *
     * @return array
     */
    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function actions(Request $request)
    {
        return [
            new ShipOrder(),
            new CancelOrder(),
        ];
    }
}
