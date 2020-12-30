<?php

declare(strict_types=1);

namespace App\Console\Commands\Bots;

use Illuminate\Console\Command;
use Telegram;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use URL;

class LinkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
    bot:link
        {bot? : Bot alias to unlink}
        {--force : Force even if already set}
    CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Registers the webhook, unless it\'s already set';

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
        $webhookUrl = URL::signedRoute('api.bots.telegram');

        // Check for a webhook
        $info = $bot->getWebhookInfo();

        // Skip if unset
        if (!empty($info['url'])) {
            $setDomain = parse_url($info['url'], \PHP_URL_HOST);
            $expectDomain = parse_url($webhookUrl, \PHP_URL_HOST);

            if ($setDomain !== $expectDomain) {
                $this->line("Webhook bound to [{$info['url']}], which doesn't match the app's domain.");

                if (!$this->option('force')) {
                    $this->error('Aborting command');
                    return 1;
                }

                $this->warn('You\'ve forced it, let\'s burn this 💩');
            }
        }

        // Prep config
        $webhookConfig = [
            'url' => $webhookUrl,
            'max_connections' => 5,
            'allowed_updates' => [
                'message',
            ],
        ];

        try {
            // Save
            $bot->setWebhook($webhookConfig);

            // And report OK
            $this->info('Webhook set');
            return 0;
        } catch (TelegramSDKException $e) {
            // Fail 😢
            $this->line('Webhook could not be set:');
            $this->error($e->getMessage());
            return 1;
        }
    }
}
