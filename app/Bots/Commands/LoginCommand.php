<?php

declare(strict_types=1);

namespace App\Bots\Commands;

use Illuminate\Support\Facades\Log;
use Telegram\Bot\Actions;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Keyboard\Keyboard;

class LoginCommand extends Command
{
    private const LOGOUT_MSG = <<<'TEXT'
    👋 Hoi <strong>%s</strong>

    Je bent al ingelogd op de bot. Typ /logout om uit te loggen.
    TEXT;

    private const LOGIN_MSG = <<<'TEXT'
    🛂 Login bij Gumbo

    Je kunt je Telegram account koppelen aan je Gumbo account om extra
    functionaliteiten uit de bot te halen.

    Ingelogde leden kunnen bijvoorbeeld de plazacam opvragen of de besloten
    activiteiten zien, en ben je een documentje vergeten? No worries, we sturen
    'm naar je op als DM.

    Klik hieronder om je accounts te koppelen.
    TEXT;

    private const LOGIN_MSG_FAIL = <<<'TEXT'
    ⚠ Inloggen niet mogelijk

    Het is helaas niet mogelijk de benodigde gegevens naar Telegram te sturen,
    neem even contact op met de DC.
    TEXT;

    /**
     * The name of the Telegram command.
     *
     * @var string
     */
    protected $name = 'login';

    /**
     * The Telegram command description.
     *
     * @var string
     */
    protected $description = 'Koppel je Telegram account';

    /**
     * Handle the activity.
     */
    public function handle()
    {
        // Send typing status
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        // Get user
        $user = $this->getUser();

        if ($user) {
            // Add debug
            Log::info('Got login prompt for logged in user {user}', compact('user'));

            // Send already logged in
            $this->replyWithMessage([
                'text' => sprintf(self::LOGOUT_MSG, $user->alias ?? $user->first_name),
                'parse_mode' => 'HTML',
            ]);

            return;
        }

        // Add debug
        Log::info('Building login prompt {user}', compact('user'));

        // Prep a keyboard
        $keyboard = (new Keyboard())->inline();
        $keyboard->row(
            Keyboard::inlineButton([
                'text' => 'Inloggen',
                'login_url' => [
                    'url' => route('account.tg.link'),
                    'request_write_access' => false,
                ],
            ]),
        );

        // Return message
        try {
            $this->replyWithMessage([
                'text' => $this->formatText(self::LOGIN_MSG),
                'parse_mode' => 'HTML',
                'reply_markup' => $keyboard,
            ]);
        } catch (TelegramSDKException $e) {
            $this->replyWithMessage([
                'text' => $this->formatText(self::LOGIN_MSG_FAIL),
                'parse_mode' => 'HTML',
            ]);
        }
    }
}
