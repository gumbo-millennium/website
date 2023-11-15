<?php

declare(strict_types=1);

namespace App\Excel\Exports\Payments;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SettlementExportCollection extends SettlementExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new SettlementExportSummarySheet($this->settlement, $this->payments, $this->refunds),
            new SettlementExportActivitiesSheet($this->settlement, $this->payments, $this->refunds),
        ];
    }
}
