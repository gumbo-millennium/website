<?php

declare(strict_types=1);

namespace App\BotMan\Messages;

use App\Models\User;
use BotMan\BotMan\BotMan;

class StartMessage extends AbstractMessage
{
    private const COMMAND_TEMPLATE = <<<'HTML'
    Welkom bij de Gumbot 🤖

    Typ /help om een overzicht te krijgen van de mogelijke commando's.
    HTML;

    /**
     * Sends a hello message
     * @param BotMan $bot
     * @param null|User $user
     * @return void
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function run(BotMan $bot, ?User $user): void
    {
        // Send hello
        $bot->reply(self::COMMAND_TEMPLATE);
    }
}
