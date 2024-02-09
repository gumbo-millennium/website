<?php

declare(strict_types=1);

namespace App\Console\Commands\Bots;

use App\Jobs\Bots\HandleUpdateJob;
use Illuminate\Console\Command;
use Telegram\Bot\Api;
use Telegram\Bot\Laravel\Facades\Telegram;

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
        assert($bot instanceof Api);

        // Check for a webhook
        $info = $bot->getWebhookInfo();
        if (! empty($info['url'])) {
            $this->error('Bot has a webhook setup, cannot listen for updates');

            return 1;
        }

        // Get identity
        $this->line('<fg=gray>Querying Telegram...</>');
        $botMe = $bot->getMe();

        $this->line("Running as Telegram bot <fg=cyan>@{$botMe->username}</> (<fg=yellow>{$botMe->id}</>)");

        // Send current command list
        $this->line('<fg=gray>Reporting command list...</>');
        $this->call('bot:update', ['bot' => $this->argument('bot')]);

        // Report and start
        $this->line('<fg=gray>Now listening for updates...</>');
        $updateParams = [
            'limit' => 1,
            'allowed_updates' => [
                'message',
                'message_reaction',
            ],
        ];

        do {
            $updates = $bot->getUpdates($updateParams);

            foreach ($updates as $update) {
                $updateParams['offset'] = $update->updateId + 1;

                $this->line("Handling update <fg=magenta>{$update->updateId}</> of type <fg=yellow>{$update->objectType()}</>...");

                HandleUpdateJob::dispatchSync($update);
            }
        } while (true);
    }
}
