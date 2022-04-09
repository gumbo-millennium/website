<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\BotQuote;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithProperties;

class BotQuoteExport implements FromCollection, ShouldAutoSize, WithColumnWidths, WithHeadings, WithProperties
{
    private Collection $quotes;

    public function __construct(Collection $quotes)
    {
        $this->quotes = $quotes;
    }

    public function properties(): array
    {
        return [
            'title' => 'Ingestuurde wist-je-datjes',
            'description' => sprintf(
                'Ingestuurde wist-je-datjes en quotes van %s tot %s',
                $this->quotes->min('created_at')->isoFormat('D MMMM YYYY'),
                $this->quotes->max('created_at')->isoFormat('D MMMM YYYY'),
            ),
            'company' => 'Gumbo Millennium',
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'User',
            'Date',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 30,
            'C' => 30,
        ];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Create dataset
        return $this->quotes
            ->sortBy('created_at')
            ->values()
            ->map(fn (BotQuote $row) => [
                $row->created_at,
                $row->display_name,
                $row->user?->name,
                $row->quote,
            ])
            ->all();
    }
}
