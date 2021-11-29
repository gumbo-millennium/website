<?php

declare(strict_types=1);

namespace App\Nova\Filters;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Laravel\Nova\Filters\Filter;

class PaymentStatusFilter extends Filter
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
                    return $query->whereHas('payments', fn ($builder) => $builder->whereNotNull('paid_at'));
                case PaymentStatus::CANCELLED:
                    return $query->whereHas('payments', fn ($builder) => $builder->whereNotNull('cancelled_at'));
                case PaymentStatus::EXPIRED:
                    return $query->whereHas('payments', fn ($builder) => $builder->where(function ($query) {
                        $query->whereNotNull('expires_at')
                            ->where('expires_at', '<', Date::now());
                    }));
                case PaymentStatus::PENDING:
                    return $query->whereHas('payments', fn ($builder) => $builder->whereNull('transaction_id'));
                case PaymentStatus::OPEN:
                    return $query->whereHas('payments', fn ($builder) => $builder->pending());
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
        return PaymentStatus::PAID;
    }
}
