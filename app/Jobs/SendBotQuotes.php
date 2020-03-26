<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\SendBotQuotes as MailSendBotQuotes;
use App\Models\BotQuote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class SendBotQuotes implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     * @return void
     */
    public function handle()
    {
        // Get a list of unsent quotes
        $quotes = BotQuote::query()
            ->whereNull('submitted_at')
            ->orderBy('created_at')
            ->with('user:id,name')
            ->get();

        // Skip if empty
        if ($quotes->isEmpty()) {
            return;
        }

        // Get e-mail address
        $recipient = config('gumbo.guote-to', 'pr@gumbo.nu');

        // Skip if empty
        if (empty($recipient)) {
            $this->fail(new RuntimeException('No quote recipient set'));
            return;
        }

        // Prepare mail
        Mail::to((string) $recipient)
            ->send(new MailSendBotQuotes($quotes));

        // Mark quotes as sent
        foreach ($quotes as $quote) {
            $quote->submitted_at = now();
            $quote->save();
        }
    }
}
