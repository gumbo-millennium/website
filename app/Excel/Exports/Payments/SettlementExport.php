<?php

declare(strict_types=1);

namespace App\Excel\Exports\Payments;

use App\Models\Payment;
use App\Models\Payments\Settlement;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Mollie\Api\Resources\Payment as MolliePayment;
use Mollie\Api\Resources\Refund as MollieRefund;

abstract class SettlementExport
{
    use Exportable;

    /**
     * Payment models for reference.
     * @var Collection<Payment>
     */
    public readonly Collection $paymentModels;

    /**
     * @param Settlement $settlement Settlement to process
     * @param Collection|MolliePayment[] $payments Mollie payments for reference
     * @param Collection|MollieRefund[] $refunds Mollie refunds for reference
     */
    public function __construct(
        public readonly Settlement $settlement,
        public readonly Collection $payments,
        public readonly Collection $refunds,
    ) {
        $allPossibleIds = Collection::make()
            ->push($payments->pluck('id'))
            ->push($refunds->pluck('paymentId'))
            ->unique()
            ->values();

        $this->paymentModels = Payment::query()
            ->where(
                fn ($query) => $query
                    ->where('provider', 'mollie')
                    ->whereIn('transaction_id', $allPossibleIds),
            )

            ->with('payable')
            ->get()
            ->keyBy('transaction_id');
    }
}
