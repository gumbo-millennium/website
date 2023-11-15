<?php

declare(strict_types=1);

namespace App\Excel\Exports\Payments;

use Maatwebsite\Excel\Concerns\FromArray;

class SettlementExportSummarySheet extends SettlementExport implements FromArray
{
    public function array(): array
    {
        return [
            ['', 'Inkomstenbron', 'Bruto inkomsten', 'Totaal transactiekosten', 'Kosten Mollie', 'Transactiekosten buffer'],
        ];
    }
}
