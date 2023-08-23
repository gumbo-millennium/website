<?php

declare(strict_types=1);

namespace App\Mail;

use App\Excel\Exports\BotQuoteExport;
use App\Helpers\Str;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Excel as ExcelApp;
use Maatwebsite\Excel\Facades\Excel;

class BotQuotesMessage extends Mailable
{
    use Queueable;
    use SerializesModels;

    private const ATTACHMENT_MIMES = [
        ExcelApp::XLSX => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
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
        $title = $this->determineSubject(
            $this->quotes->min('created_at'),
            $this->quotes->max('created_at'),
        );

        // Add export
        $this->attachData(
            Excel::raw(new BotQuoteExport($this->quotes), ExcelApp::XLSX),
            (string) Str::of($title)->slug()->append('.xlsx'),
            ['mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', ],
        );

        // Return mail
        return $this->markdown('mail::quotes', [
            'quotesList' => $this->formatQuotes($this->quotes),
        ])->subject($title);
    }

    /**
     * Format quotes into a list of quotes per day.
     */
    private function formatQuotes(Collection $quotes): iterable
    {
        return $quotes
            ->groupBy(fn ($quote) => $quote->created_at->format('Y-m-d'))
            ->map(fn ($group) => [
                $group->first()->created_at->isoFormat('ddd D MMMM \'YY'), // title
                $group, // quotes
            ])->sortKeys();
    }

    /**
     * Build a subject that properly refects the content in the mail.
     */
    private function determineSubject(DateTimeInterface $start, DateTimeInterface $end): string
    {
        // Ensure carbon objects
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);

        // Use the first quote's week number if the interval is about one week.
        if ($start->diffInDays($end) <= 8) {
            $translationKey = 'week';
        } elseif ($start->month === $end->month) {
            $translationKey = 'month';
        } elseif ($start->diffInMonths($end) === 1) {
            $translationKey = 'adjacent-months';
        } else {
            $translationKey = 'spanning-months';
        }

        // Get translation params
        $params = [
            'week1' => $start->weekOfYear,
            'week2' => $start->weekOfYear,
            'month1' => $start->isoFormat('MMMM'),
            'month2' => $end->isoFormat('MMMM'),
            'year1' => $start->year,
            'year2' => $end->year,
        ];

        return __("gumbo.bot-quotes.titles.{$translationKey}", $params);
    }
}
