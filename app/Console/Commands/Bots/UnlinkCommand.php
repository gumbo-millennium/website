<?php

declare(strict_types=1);

namespace App\Console\Commands\Bots;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Laravel\Facades\Telegram;

class UnlinkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
    bot:unlink
        {bot? : Bot alias to unlink}
        {--force : Force even if URL doesn\'t match}
    CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disables the webhook, checks if the webhook URL matches our expected URL';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get bot
        $bot = Telegram::bot($this->argument('bot'));
        \assert($bot instanceof Api);

        // Get expected webhook URL
        $expected = URL::signedRoute('api.bots.telegram');

        // Check for a webhook
        $info = $bot->getWebhookInfo();

        // Skip if unset
        if (empty($info['url'])) {
            $this->info('No webhook is set');
            return 255;
        }

        // Skip if mismatch
        if ($info['url'] !== $expected) {
            $this->line("Bot webhook URL [{$info['url']}] does not match expected URL, not removing.");

            // Well, unless forced
            if (!$this->option('force')) {
                return 1;
            }
            $this->warn('You\'ve forced it, let\'s burn this 💩');
        }

        try {
            // Save
            $bot->removeWebhook();

            // And report OK
            $this->info('Webhook removed');
            return 0;
        } catch (TelegramSDKException $e) {
            // Fail 😢
            $this->line('Webhook could not be removed:');
            $this->error($e->getMessage());
            return 1;
        }
    }
}
