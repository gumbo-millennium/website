<?php

declare(strict_types=1);

namespace App\BotMan\Messages;

use App\BotMan\Traits\HasGroupCheck;
use App\Helpers\Arr;
use App\Models\User;
use BotMan\BotMan\BotMan;

class FallbackMessage extends AbstractMessage
{
    use HasGroupCheck;

    private const DEFAULT_MESSAGE = 'Sorry, dit commando ken ik niet.';

    private const QUOTE_LIST = [
        'Klootviool, wat doe je!?',
        'Waar zat je met je lul, toen je zei dat Born To Be Alive van The Village People was?',
        ['Hoe gaat \'ie met jou?', 1100, 'Met mij best wel wauw.'],
        'Goedemiddag klootviool, met Michael Nicht.',
        ['Ik voel me een beetje opgeblazen...', 1500, 'Maar dat maakt niet uit, ik heb trek in een koekje.'],
        'Baas, ik voel me gebruikt.',
        'Heeft er ooit iemand <i>iets</i> bereikt met leren?'
    ];

    /**
     * Returns a 'you're doing it wrong' prompt.
     * @param BotMan $bot
     * @param null|User $user
     * @return void
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function run(BotMan $bot, ?User $user): void
    {
        // Skip if not a command
        $message = $bot->getMessage();
        if (empty($message->getText()) || substr($message->getText(), 0, 1) !== '/') {
            return;
        }

        // Skip if in a group and not mentioned
        if ($this->isInGroup() && !$this->isMentioned()) {
            return;
        }

        // Only quote movies if we really have to
        $quoteFactor = random_int(1, 100);
        if ($quoteFactor < 95) {
            $bot->reply(self::DEFAULT_MESSAGE);
            return;
        }

        $quote = Arr::random(self::QUOTE_LIST);

        if (!is_array($quote)) {
            $bot->reply($quote);
            return;
        }

        foreach ($quote as $line) {
            if (is_string($line)) {
                $bot->reply($line);
                continue;
            } elseif (is_int($line)) {
                $bot->typesAndWaits($line / 1000);
            }
        }
    }
}
