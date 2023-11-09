<?php

declare(strict_types=1);

namespace App\Bots\Commands;

use App\Helpers\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Telegram\Bot\Commands\Command as TelegramCommand;

/**
 * @codeCoverageIgnore
 */
abstract class Command extends TelegramCommand
{
    use Traits\UsesPreloadedGifs;
    use Traits\UsesRateLimits;
    use Traits\UsesUsersAndChats;

    /**
     * Runs a string through sprintf, and unwraps single newlines.
     */
    public function formatText(string $text, ...$args): string
    {
        $out = sprintf($text, ...$args);

        return preg_replace('/(?<!\n)\n(?=\S)/', ' ', $out);
    }

    protected function getCommandName(): ?string
    {
        $message = $this->getUpdate()->getMessage()->getText();

        $options = [$this->getName(), ...$this->getAliases()];

        foreach ($options as $commandName) {
            if (Str::contains($message, "/{$commandName}")) {
                return $commandName;
            }
        }
    }

    protected function getBotUsername(): string
    {
        return Cache::remember('telegarm.bot.username', Date::now()->addDay(), function () {
            $me = $this->getTelegram()->getMe();

            return (string) ($me->username ?? $me->id);
        });
    }

    protected function getCommandBody(): ?string
    {
        $command = $this->getCommandName();
        $username = $this->getBotUsername();
        $message = $this->getUpdate()->getMessage()->getText();

        $fullCommand = "/{$command}@{$username}";
        $shortCommand = "/{$command}";

        $fullPosition = mb_stripos($message, $fullCommand);
        $shortPosition = mb_stripos($message, $shortCommand);

        if ($fullPosition !== false) {
            return trim(mb_substr($message, $fullPosition + mb_strlen($fullCommand)));
        }

        if ($shortPosition !== false) {
            return trim(mb_substr($message, $shortPosition + mb_strlen($shortCommand)));
        }

        return $message;
    }
}
