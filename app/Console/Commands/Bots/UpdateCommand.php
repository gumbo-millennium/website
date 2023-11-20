<?php

declare(strict_types=1);

namespace App\Console\Commands\Bots;

use App\Helpers\Str;
use Illuminate\Console\Command;
use Telegram\Bot\Api;
use Telegram\Bot\Commands\CommandInterface;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Laravel\Facades\Telegram;

class UpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
        bot:update
            {bot? : Bot alias to update on}
        CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Informs Telegram about the commands registered';

    /**
     * Execute the console command.
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
            $subcommands = [$command->getName(), ...$command->getAliases()];

            foreach ($subcommands as $commandName) {
                if (method_exists($command, 'getDescriptionFor')) {
                    $mapping[] = [
                        'command' => $commandName,
                        'description' => $command->getDescriptionFor($commandName),
                    ];

                    continue;
                }

                $mapping[] = [
                    'command' => $commandName,
                    'description' => $command->getDescription(),
                ];
            }
        }

        // Ensure all commands end with a period
        foreach ($mapping as &$map) {
            $map['description'] = Str::finish($map['description'], '.');
        }

        // Write all descriptions
        foreach ($mapping as $map) {
            $this->line("<info>/{$map['command']}</>: {$map['description']}");
        }

        try {
            // Save
            $bot->setMyCommands([
                'commands' => $mapping,
            ]);

            // And report OK
            $this->info('Commands updated');

            return 0;
        } catch (TelegramSDKException $botException) {
            // Fail 😢
            $this->line('Failed to update commands:');
            $this->error($botException->getMessage());

            return 1;
        }
    }
}
