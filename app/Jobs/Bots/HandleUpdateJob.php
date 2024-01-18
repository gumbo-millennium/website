<?php

declare(strict_types=1);

namespace App\Jobs\Bots;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class HandleUpdateJob
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public readonly Update $update)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Handle reaction
        if ($this->update->isType('message_reaction')) {
            HandleUpdatedReactionJob::dispatchSync($this->update);
        }

        // Handle message
        if ($this->update->isType('message')) {
            Telegram::bot()->processCommand($this->update);
        }
    }
}
