<?php

declare(strict_types=1);

namespace App\BotMan\Messages;

use App\BotMan\Traits\HasGroupCheck;
use App\Models\User;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;

class DebugMessage extends AbstractMessage
{
    use HasGroupCheck;

    /**
     * Returns a 'you're doing it wrong' prompt.
     * @param BotMan $bot
     * @param null|User $user
     * @return void
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function run(BotMan $bot, ?User $user): void
    {
        // If not in debug, forward
        if (!config('app.debug')) {
            $fallbackCmd = new FallbackMessage();
            $fallbackCmd($bot);
            return;
        }

        // Get message
        $message = $bot->getMessage();
        \assert($message instanceof IncomingMessage);

        $bot->reply('Debug messsges ☺');
        $bot->reply(sprintf('Group chat: %s', $this->isInGroup() ? 'Yes' : 'No'));
        $bot->reply(sprintf('Mentioned: %s', $this->isMentioned() ? 'Yes' : 'No'));
        $bot->reply("From: {$message->getSender()}");
        $bot->reply("To: {$message->getRecipient()}");
    }
}
