<?php

declare(strict_types=1);

namespace App\Excel\Exports\Payments;

use App\Models\Enrollment;
use Illuminate\Support\Collection;
use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromArray;

class SettlementExportSummarySheet extends SettlementExport implements FromArray
{
    public function array(): array
    {
        $rows = Collection::make();

        $models = $this->paymentModels;

        foreach ($this->payments as $payment) {
            /** @var Payment $model */
            $model = $models->get($payment->id) ?? $models->get($payment->orderId);

            if (! $model || ! $model->payable instanceof Enrollment) continue;

            $enrollment = $model->payable;
            $activity = $enrollment->activity;

            $activityRow = $rows->get($activity->id, [
                'id' => $activity->id,
                'name' => $activity->name,
                'group' => $activity->group?->name ?? 'n/a',
                'gross' => money_value(0),
                'trans' => money_value(0),
                'mollie' => money_value(0),
                'buffer' => money_value(0),
            ]);

            $activityRow['gross'] = $activityRow['gross']->add(money_value($enrollment->total_price));
            $activityRow['fees'] = $activityRow['fees']->add(money_value($enrollment->total_price - $enrollment->price));
            $activityRow['mollie'] = $activityRow['mollie']->add(money_value($payment->))
        }

        return [
            ['', 'Inkomstenbron', 'Bruto inkomsten', 'Totaal transactiekosten', 'Kosten Mollie', 'Transactiekosten buffer'],
        ];
    }
}
