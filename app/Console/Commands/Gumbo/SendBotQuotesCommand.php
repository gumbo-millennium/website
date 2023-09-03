<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo;

use App\Jobs\SendBotQuotes;
use Illuminate\Console\Command;

class SendBotQuotesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gumbo:send-quotes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends the bot quotes instantly';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Send quotes
        SendBotQuotes::dispatchSync();

        // Return OK
        $this->info('Quotes have been sent.');
    }
}
