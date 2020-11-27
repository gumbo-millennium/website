<?php

declare(strict_types=1);

namespace App\Bots\Commands;

use Illuminate\Support\Facades\Cache;

class SackCommand extends Command
{
    /**
     * The name of the Telegram command.
     * @var string
     */
    protected $name = 'royatieverzoek';

    /**
     * The Telegram command description.
     * @var string
     */
    protected $description = 'Stuurt iemand De Laan uit';

    /**
     * Command Argument Pattern
     * @var string
     */
    protected $pattern = '[^\s].+';

    /**
     * Handle the activity
     */
    public function handle()
    {
        // Get TG user
        $tgUser = $this->getTelegramUser();

        // Rate limit
        $cacheKey = sprintf('tg.sack.%s', $tgUser->id);
        if (Cache::get($cacheKey) > now()) {
            $this->replyWithMessage([
                'text' => '⏸ Rate limited (1x per ALV)'
            ]);
            return;
        }

        // Prep rate limit
        Cache::put($cacheKey, now()->addMinute(), now()->addWeek());

        // Get user and check member rights
        $user = $this->getUser();
        if (!$this->ensureIsMember($user)) {
            return;
        }

        // Check the quote
        $target = ucwords(trim($this->arguments['custom'] ?? ''));

        // Get random lines
        $format = sprintf(
            '😡 %s dient een royatieverzoek in voor %s.',
            $user->name,
            $target
        );

        // Send as-is
        $this->replyWithMessage([
            'text' => $format
        ]);
    }
}
