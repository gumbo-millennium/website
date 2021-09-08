<?php

declare(strict_types=1);

namespace App\Bots\Commands;

use Telegram\Bot\Commands\CommandInterface;

/**
 * @codeCoverageIgnore
 */
class HelpCommand extends Command
{
    private const MSG = <<<'TEXT'
    🤖 Beep Boop, this I can do:

    %s
    TEXT;

    private const HIDDEN_CMDS = [
        'help',
        'start',
    ];

    /**
     * The name of the Telegram command.
     *
     * @var string
     */
    protected $name = 'help';

    /**
     * The Telegram command description.
     *
     * @var string
     */
    protected $description = 'Toont de beschikbare commando\'s';

    /**
     * Handle the activity.
     */
    public function handle()
    {
        $commands = $this->getTelegram()->getCommands();

        $hidden = self::HIDDEN_CMDS;
        $hidden[] = $this->getUser() ? 'login' : 'logout';

        $texts = [];
        foreach ($commands as $command) {
            \assert($command instanceof CommandInterface);

            // Skip hiddens
            if (\in_array($command->getName(), $hidden, true)) {
                continue;
            }

            // Print command
            $texts[] = sprintf('/%s - %s', $command->getName(), $command->getDescription());
        }

        // Sort
        sort($texts);

        // Send as-is
        $this->replyWithMessage([
            'text' => sprintf(self::MSG, implode(\PHP_EOL, $texts)),
        ]);
    }
}
