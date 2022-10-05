<?php

declare(strict_types=1);

namespace App\Mail;

use App\Excel\Exports\BotQuoteExport;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Excel as ExcelApp;
use Maatwebsite\Excel\Facades\Excel;

class SendBotQuotes extends Mailable
{
    use Queueable;
    use SerializesModels;

    private const ATTACHMENT_MIMES = [
        ExcelApp::ODS => 'application/vnd.oasis.opendocument.spreadsheet',
        ExcelApp::XLSX => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ExcelApp::CSV => 'text/csv',
    ];

    protected Collection $quotes;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Collection $quotes)
    {
        $this->quotes = $quotes;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Set subject
        $title = $this->setSubject(
            $this->quotes->min('created_at'),
            $this->quotes->max('created_at'),
        );

        // Get attachments
        $attachments = $this->buildQuoteFiles($this->quotes, $title);

        // Add attachments
        foreach ($attachments as $attachment) {
            $this->attach($attachment['path'], [
                'as' => $attachment['filename'],
                'mime' => $attachment['mimetype'],
            ]);
        }

        // Get list
        $quotesList = $this->formatQuotes($this->quotes);

        // Return mail
        return $this->markdown('mail::quotes', compact('quotesList'));
    }

    /**
     * Creates a list of quotes in XSLX, ODT and CSV format.
     *
     * @return array<string>
     */
    public function buildQuoteFiles(Collection $quotes, string $title): array
    {
        // Create export
        $export = new BotQuoteExport($this->quotes);

        // Write files
        foreach (self::ATTACHMENT_MIMES as $format => $mime) {
            $path = tempnam(sys_get_temp_dir(), "quotes-{$format}-");

            file_put_contents($path, Excel::raw($export, $format));

            $filename = "{$title}.{$format}";
            $files[] = [
                'path' => $path,
                'filename' => $filename,
                'mimetype' => $mime,
            ];
        }

        // Return files
        return $files;
    }

    private function formatQuotes(Collection $quotes): array
    {
        $quotesList = [];
        foreach ($quotes as $quote) {
            // Prep key
            $quoteDate = $quote->created_at;
            $quoteKey = $quoteDate->format('Y-m-d');

            // Create entry if missing
            if (! isset($quotesList[$quoteKey])) {
                $quotesList[$quoteKey] = [
                    'title' => $quoteDate->isoFormat('dddd D MMMM'),
                    'quotes' => [],
                ];
            }

            $quotesList[$quoteKey]['quotes'][] = $quote;
        }

        // Convert to list()-able format
        $quotesList = array_map(static fn ($row) => [$row['title'], $row['quotes']], $quotesList);

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
        if ($start->month + 1 === $end->month) {
            $this->subject("[gumbo.nu] De wist-je-datjes van {$startLabel} en {$endLabel}");

            return "{$baseName} - {$startLabel} en {$endLabel}";
        }

        // Return range otherwise
        $this->subject("[gumbo.nu] De wist-je-datjes van {$startLabel} t/m {$endLabel}");

        return "{$baseName} - {$startLabel} tm {$endLabel}";
    }
}
