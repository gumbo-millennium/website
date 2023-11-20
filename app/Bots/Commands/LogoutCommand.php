<?php

declare(strict_types=1);

namespace App\Bots\Commands;

use Illuminate\Support\Facades\Log;
use Telegram\Bot\Actions;

/**
 * @codeCoverageIgnore
 */
class LogoutCommand extends Command
{
    private const LOGOUT_OK = <<<'TEXT'
    🚪 Je bent uitgelogd.
    TEXT;

    private const LOGOUT_FAIL = <<<'TEXT'
    Je was niet eens ingelogd... 😅
    TEXT;

    /**
     * The name of the Telegram command.
     */
    protected string $name = 'logout';

    /**
     * The Telegram command description.
     */
    protected string $description = 'Ontkoppel je Telegram account';

    /**
     * Handle the activity.
     */
    public function handle()
    {
        // Send typing status
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        // Get user
        $user = $this->getUser();

        if (! $user) {
            // Not logged in
            $this->replyWithMessage([
                'text' => self::LOGOUT_FAIL,
                'parse_mode' => 'HTML',
            ]);

            return;
        }

        // Add debug
        Log::info('Logging out {user}', compact('user'));

        // Logout
        $user->telegram_id = null;
        $user->save();

        // Send OK
        $this->replyWithMessage([
            'text' => self::LOGOUT_OK,
            'parse_mode' => 'HTML',
        ]);
    }
}
