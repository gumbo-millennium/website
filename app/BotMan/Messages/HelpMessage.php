<?php

declare(strict_types=1);

namespace App\BotMan\Messages;

use App\Models\User;
use BotMan\BotMan\BotMan;

class HelpMessage extends AbstractMessage
{
    private const COMMAND_TEMPLATE = <<<'HTML'
    Beschikbare commando's 🤖

    %s
    HTML;

    private const COMMAND_LIST = [
        'start' => 'Laat welkomstbericht zien.',
        'help' => 'Laat de commando\'s zien.',
        'activiteiten' => 'Laat de komende activiteiten zien.',
        'plazacam' => 'Laat de Plazacam zien.',
        'koffiecam' => 'Laat de koffiecam zien.',
        'wjd' => 'Stuur een wist-je-datje of quote in.',
        'dennisbier' => 'Dennis bier (alleen in privéchat)',
    ];

    /**
     * Sends a list of commands to the user
     * @param BotMan $bot
     * @param null|User $user
     * @return void
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function run(BotMan $bot, ?User $user): void
    {
        // Prep list
        $commands = [];
        foreach (self::COMMAND_LIST as $command => $help) {
            $commands[] = "/{$command} - {$help}";
        }

        // Send list
        $bot->reply(sprintf(self::COMMAND_TEMPLATE, implode(PHP_EOL, $commands)));
    }
}
