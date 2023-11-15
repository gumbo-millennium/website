<?php

declare(strict_types=1);

namespace App\Excel\Exports\Payments;

use App\Models\Payments\Settlement;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Mollie\Api\Resources\Payment as MolliePayment;
use Mollie\Api\Resources\Refund as MollieRefund;

abstract class SettlementExport
{
    use Exportable;

    /**
     * @param Settlement $settlement Settlement to process
     * @param Collection<MolliePayment> $payments Mollie payments for reference
     * @param Collection<MollieRefund> $refunds Mollie refunds for reference
     */
    public function __construct(
        public readonly Settlement $settlement,
        public readonly Collection $payments,
        public readonly Collection $refunds,
    ) {
        // no-op
    }
}
