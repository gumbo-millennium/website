<?php

declare(strict_types=1);

namespace App\Nova\Filters;

use App\Contracts\Payments\PayableModel;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class PayableStatusFilter extends Filter
{
    /**
     * The displayable name of the filter.
     *
     * @var string
     */
    public $name = 'Betaalstatus';

    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    /**
     * Apply the filter to the given query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return $query->wherePaymentStatus($value);
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(Request $request)
    {
        return collect([
            PayableModel::STATUS_UNKNOWN,
            PayableModel::STATUS_OPEN,
            PayableModel::STATUS_PAID,
            PayableModel::STATUS_CANCELLED,
            PayableModel::STATUS_COMPLETED,
        ])->mapWithKeys(fn ($val) => [__("gumbo.payment-status.{$val}") => $val])->all();
    }

    /**
     * Set the default options for the filter.
     *
     * @return array|mixed
     */
    public function default()
    {
        return PayableModel::STATUS_PAID;
    }
}
