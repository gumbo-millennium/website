<?php

declare(strict_types=1);

namespace App\Mail;

use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class SendBotQuotes extends Mailable
{
    use Queueable;
    use SerializesModels;

    private const ATTACHMENT_MIMES = [
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'csv' => 'text/csv',
    ];

    protected Collection $quotes;

    /**
     * Create a new message instance.
     * @return void
     */
    public function __construct(Collection $quotes)
    {
        $this->quotes = $quotes;
    }

    /**
     * Build the message.
     * @return $this
     */
    public function build()
    {
        // Set subject
        $title = $this->setSubject(
            $this->quotes->min('created_at'),
            $this->quotes->max('created_at')
        );

        // Get attachments
        $attachments = $this->buildQuoteFiles($this->quotes, $title);

        // Add attachments
        foreach ($attachments as $attachment) {
            $this->attach($attachment['path'], [
                'as' => $attachment['filename'],
                'mime' => $attachment['mimetype']
            ]);
        }

        // Get list
        $quotesList = $this->formatQuotes($this->quotes);

        // Return mail
        return $this->markdown('mail::quotes', compact('quotesList'));
        ;
    }

    private function formatQuotes(Collection $quotes): array
    {
        $quotesList = [];
        foreach ($quotes as $quote) {
            // Prep key
            $quoteDate = $quote->created_at;
            $quoteKey = $quoteDate->format('Y-m-d');

            // Create entry if missing
            if (!isset($quotesList[$quoteKey])) {
                $quotesList[$quoteKey] = [
                    'title' => $quoteDate->isoFormat('ddd DD MMMM'),
                    'quotes' => []
                ];
            }

            $quotesList[$quoteKey]['quotes'][] = $quote;
        }

        // Convert to list()-able format
        $quotesList = array_map(static fn($row) => [$row['title'], $row['quotes']], $quotesList);

        // Sort entries by key
        \ksort($quotesList);

        // Return it
        return $quotesList;
    }

    private function setSubject(DateTimeInterface $start, DateTimeInterface $end): string
    {
        // Ensure carbon objects
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);

        // Update subject according to scope
        $startLabel = $start->isoFormat('MMMM');
        $endLabel = $end->isoFormat('MMMM');
        $isoLabel = $start->format('Y-m');

        // Prep a basename
        $baseName = "wist-je-datjes {$isoLabel}";

        // Return just one month if it is just one month
        if ($start->month === $end->month) {
            $this->subject("[gumbo.nu] De wist-je-datjes van {$startLabel}");
            return "{$baseName} - {$startLabel}";
        }

        // Return "en"-separated if two months
        if (($start->month + 1) === $end->month) {
            $this->subject("[gumbo.nu] De wist-je-datjes van {$startLabel} en {$endLabel}");
            return "{$baseName} - {$startLabel} en {$endLabel}";
        }

        // Return range otherwise
        $this->subject("[gumbo.nu] De wist-je-datjes van {$startLabel} t/m {$endLabel}");
        return "{$baseName} - {$startLabel} tm {$endLabel}";
    }

    /**
     * Creates a list of quotes in XSLX, ODT and CSV format
     * @param Collection $quotes
     * @return array<string>
     */
    public function buildQuoteFiles(Collection $quotes, string $title): array
    {
        // Add header row
        $data = [
            ['Datum', 'Weergavenaam', 'Bekende naam', 'Quote']
        ];

        // Create dataset
        $quotes = $quotes->sortBy('created_at');
        foreach ($quotes as $row) {
            $data[] = [
                $row->created_at,
                $row->display_name,
                optional($row->user)->name,
                $row->quote
            ];
        }

        // Create worksheet
        $spreadsheet = new Spreadsheet();

        // Set properties
        $properties = $spreadsheet->getProperties();
        $properties->setTitle($title);
        $properties->setModified($quotes->max('created_at')->getTimestamp());
        $properties->setCompany('Gumbo Millennium');

        // Get sheet and assign data
        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->fromArray($data, null, 'A1', true);
        $activeSheet->setTitle('Wist-je-datjes');
        $activeSheet->freezePane('A2');
        $activeSheet->setAutoFilterByColumnAndRow(1, 1, count($data[0]), count($data));

        // Assign colum sizes
        $activeSheet->getColumnDimension('A')->setWidth(30);
        $activeSheet->getColumnDimension('B')->setWidth(30);
        $activeSheet->getColumnDimension('C')->setWidth(30);
        $activeSheet->getColumnDimension('D')->setAutoSize(true);

        // Prep writers
        $files = [];

        // Write files
        foreach (self::ATTACHMENT_MIMES as $format => $mime) {
            $path = \tempnam(\sys_get_temp_dir(), "quotes-{$format}-");
            $writer = IOFactory::createWriter($spreadsheet, \ucfirst($format));
            $writer->save($path);

            $filename = "{$title}.{$format}";
            $files[] = [
                'path' => $path,
                'filename' => $filename,
                'mimetype' => $mime
            ];
        }

        // Return files
        return $files;
    }
}
