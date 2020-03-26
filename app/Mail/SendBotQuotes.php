<?php

declare(strict_types=1);

namespace App\Mail;

use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SendBotQuotes extends Mailable
{
    use Queueable;
    use SerializesModels;

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
        $this->setSubject(
            $this->quotes->min('created_at'),
            $this->quotes->max('created_at')
        );

        // Get list
        $quotesList = $this->formatQuotes($this->quotes);

        // Return mail
        return $this->markdown('mail::quotes', compact('quotesList'));
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

    private function setSubject(DateTimeInterface $start, DateTimeInterface $end): void
    {
        // Ensure carbon objects
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);

        // Update subject according to scope
        $startLabel = $start->isoFormat('MMMM');
        $endLabel = $end->isoFormat('MMMM');

        // Return just one month if it is just one month
        if ($start->month === $end->month) {
            $this->subject("[gumbo.nu] De wist-je-datjes van {$startLabel}");
            return;
        }

        // Return "en"-separated if two months
        if (($start->month + 1) === $end->month) {
            $this->subject("[gumbo.nu] De wist-je-datjes van {$startLabel} en {$endLabel}");
            return;
        }

        // Return range otherwise
        $this->subject("[gumbo.nu] De wist-je-datjes van {$startLabel} t/m {$endLabel}");
    }
}
