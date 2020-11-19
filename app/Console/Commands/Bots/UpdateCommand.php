<?php

declare(strict_types=1);

namespace App\Console\Commands\Bots;

use Illuminate\Console\Command;
use Telegram;
use Telegram\Bot\Api;
use Telegram\Bot\Commands\CommandInterface;

class UpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = <<<'CMD'
    bot:update
        {bot? : Bot alias to update on}
    CMD;

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Informs Telegram about the commands registered';

    /**
     * Execute the console command.
     * @return mixed
     */
    public function handle()
    {
        // Get bot
        $bot = Telegram::bot($this->argument('bot'));
        \assert($bot instanceof Api);

        // Check for a webhook
        $commands = $bot->getCommands();
        if (empty($commands)) {
            $this->error('This bot has no commands');
            return 1;
        }

        // Report and start
        $this->line('Registering new commands...');

        // Prep list
        $mapping = [];
        foreach ($commands as $command) {
            \assert($command instanceof CommandInterface);
            $template = [
                'command' => $command->getName(),
                'description' => $command->getDescription()
            ];
            $mapping[] = $template;
            foreach ($command->getAliases() as $alias) {
                $mapping[] = array_merge($template, ['command' => $alias]);
            }
        }

        foreach ($mapping as $map) {
            $this->line("<info>/{$map['command']}</>: {$map['description']}");
        }

        try {
            // Save
            $bot->setMyCommands([
                'commands' => $mapping
            ]);

            // And report OK
            $this->info('Commands updated');
            return 0;
        } catch (TelegramSDKException $e) {
            // Fail 😢
            $this->line('Webhook could not be removed:');
            $this->error($e->getMessage());
            return 1;
        }
    }
}
