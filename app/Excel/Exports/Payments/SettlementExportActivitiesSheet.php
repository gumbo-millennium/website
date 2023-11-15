<?php

declare(strict_types=1);

namespace App\Excel\Exports\Payments;

use Maatwebsite\Excel\Concerns\FromCollection;

class SettlementExportActivitiesSheet extends SettlementExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect();
    }
}
