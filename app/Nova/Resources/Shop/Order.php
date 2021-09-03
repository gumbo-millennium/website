<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Contracts\Payments\PayableModel;
use App\Models\Shop\Order as Model;
use App\Nova\Actions\Shop\CancelOrder;
use App\Nova\Actions\Shop\ShipOrder;
use App\Nova\Actions\Shop\UpdateOrder;
use App\Nova\Fields\Price;
use App\Nova\Filters\PayableStatusFilter;
use App\Nova\Resources\Resource;
use App\Nova\Resources\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Laravel\Nova\Fields;
use Laravel\Nova\Http\Requests\ActionRequest;

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
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
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
            Fields\ID::make(__('ID'), 'id')
                ->onlyOnDetail(),

            Fields\Text::make(__('Order Number'), 'number'),

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

            Fields\Text::make('Mollie link', function () {
                if (! $this->payment_id) {
                    return null;
                }

                return sprintf(
                    '<a href="%s" target="_blank">%s</a>',
                    URL::route('admin.mollie.orders', $this->id),
                    __('View order in Mollie'),
                );
            })->onlyOnDetail()->asHtml(),

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
    public function actions(Request $request)
    {
        return [
            new UpdateOrder(),
            new ShipOrder(),
            new CancelOrder(),
        ];
    }

    /**
     * Get the filters available on the entity.
     *
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            new PayableStatusFilter(),
        ];
    }

    /**
     * Determine if the current user can update the given resource.
     *
     * @return bool
     */
    public function authorizedToUpdate(Request $request)
    {
        if ($request instanceof ActionRequest) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the current user can delete the given resource.
     *
     * @return bool
     */
    public function authorizedToDelete(Request $request)
    {
        if ($request instanceof ActionRequest) {
            return true;
        }

        return false;
    }
}
