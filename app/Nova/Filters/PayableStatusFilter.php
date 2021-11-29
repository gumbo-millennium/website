<?php

declare(strict_types=1);

namespace App\Nova\Filters;

use App\Contracts\Payments\PayableModel;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
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
        return $query->where(function (Builder $query) use ($value) {
            switch ($value) {
                case PaymentStatus::PAID:
                    return $query->wherePaid();
                case PaymentStatus::CANCELLED:
                    return $query->whereCancelled();
                case PaymentStatus::EXPIRED:
                    return $query->whereExpired();
                case PaymentStatus::PENDING:
                    return $query->wherePending();
                case PaymentStatus::OPEN:
                    return $query->whereOpen();
            }
        });
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(Request $request)
    {
        return collect([
            PaymentStatus::PENDING,
            PaymentStatus::OPEN,
            PaymentStatus::PAID,
            PaymentStatus::CANCELLED,
            PaymentStatus::EXPIRED,
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
