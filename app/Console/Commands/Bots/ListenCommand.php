<?php

declare(strict_types=1);

namespace App\Console\Commands\Bots;

use Illuminate\Console\Command;
use Telegram;
use Telegram\Bot\Api;

class ListenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
    bot:listen
        {bot? : Bot alias to listen on}
    CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen for changes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get bot
        $bot = Telegram::bot($this->argument('bot'));
        \assert($bot instanceof Api);

        // Check for a webhook
        $info = $bot->getWebhookInfo();
        if (! empty($info['url'])) {
            $this->error('Bot has a webhook setup, cannot listen for updates');

            return 1;
        }

        // Send current command list
        $this->line('Reporting command list');
        $this->call('bot:update', ['bot' => $this->argument('bot')]);

        // Report and start
        $this->line('Now listening for updates');
        do {
            $bot->commandsHandler(false);
        } while (true);
    }
}
