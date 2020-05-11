<?php

declare(strict_types=1);

namespace App\BotMan\Messages;

use App\BotMan\Traits\HasBlacklistCheck;
use App\Models\BotUserLink;
use App\Models\User;
use BotMan\BotMan\BotMan;
use Illuminate\Support\Facades\Cache;

/**
 * An abstract message
 */
abstract class AbstractMessage
{
    use HasBlacklistCheck;

    private BotMan $bot;

    protected function getBot(): BotMan
    {
        return $this->bot;
    }

    protected function setBot(BotMan $bot): void
    {
        $this->bot = $bot;
    }

    /**
     * Processing an incoming message from the optional user via the given bot
     * @param BotMan $bot
     * @param null|User $user
     * @return void
     */
    abstract public function run(BotMan $bot, ?User $user): void;

    /**
     * Receives a message, locates the corresponding user and forwards the call to run.
     * @param BotMan $bot
     * @return void
     */
    public function __invoke(BotMan $bot): void
    {
        // Assign bot
        $this->setBot($bot);

        // Check blacklist
        if ($this->isBlacklisted()) {
            return;
        }

        // Get message
        $message = $bot->getMessage();

        // Find user
        $cacheKey = "botman.users.{$message->getConversationIdentifier()}";
        $user = Cache::remember($cacheKey, now()->addMinutes(60), static function () use ($message, $bot) {
            $link = BotUserLink::with('user')
                ->whereDriverId($bot->getDriver()->getName(), $message->getRecipient())
                ->first();

            return $link ? $link->user : null;
        });

        // Send to run command
        $this->run($bot, $user);
    }
}
