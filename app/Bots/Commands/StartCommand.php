<?php

declare(strict_types=1);

namespace App\Bots\Commands;

/**
 * @codeCoverageIgnore
 */
class StartCommand extends Command
{
    private const MSG = <<<'TEXT'
    😄 Welkom bij de Gumbot :)

    Typ /help om de commando's te zien, of /login om in te loggen op de bot.
    TEXT;

    /**
     * The name of the Telegram command.
     */
    protected string $name = 'start';

    /**
     * The Telegram command description.
     */
    protected string $description = 'Toont het welkomstbericht';

    /**
     * Handle the activity.
     */
    public function handle()
    {
        // Send as-is
        $this->replyWithMessage([
            'text' => $this->formatText(self::MSG),
        ]);
    }
}
