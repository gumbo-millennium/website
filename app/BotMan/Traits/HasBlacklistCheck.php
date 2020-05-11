<?php

declare(strict_types=1);

namespace App\BotMan\Traits;

use BotMan\BotMan\BotMan;

trait HasBlacklistCheck
{
    /**
     * Returns if this is sent in a group
     * @return bool
     */
    protected function isBlacklisted(): bool
    {
        // Get bot
        $bot = $this->getBot();
        \assert($bot instanceof BotMan);

        // Check banned
        $userStore = $bot->userStorage();
        $bannedState = $userStore->get('banned');
        if ($bannedState === true || $bannedState < time()) {
            // Set timestamp if missing
            if ($bannedState === true) {
                $expire = now()->addDays(4);
                $bot->reply(sprintf(
                    'Je bent geblacklist tot %s.',
                    $expire->format('Y-m-d H:i:s (T)')
                ));
                $userStore->save(['banned' => $expire->getTimestamp()]);
            }
            return true;
        }

        // Unknown
        return false;
    }
}
