<?php

declare(strict_types=1);

namespace App\Bots\Commands;

use App\Models\BotQuote;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Actions;
use Telegram\Bot\Keyboard\Keyboard;

class QuoteCommand extends Command
{
    private const REPLY_INVALID = <<<'MSG'
    Geef wist-je-datje 😠

    <code>/wjd [bericht]</code>
    MSG;

    private const REPLY_GUEST_THROTTLED = <<<'MSG'
    ⏰ Sorry, je mag nog geen wist-je-datje insturen.

    Log in via /login om deze beperking weg te halen.
    MSG;

    private const REPLY_GUEST = <<<'MSG'
    Je wist-je-datje is opgeslagen. 🤗

    Je bent niet ingelogd, dus je kan maximaal 1 wist-je-datje per
    dag insturen. Login via /login om deze limiet te verwijderen.
    MSG;

    private const REPLY_USER = <<<'MSG'
    Je wist-je-datje is opgeslagen.

    Wil je jouw wist-je-datje historie bekijken? Dat kan!
    MSG;

    /**
     * The name of the Telegram command.
     *
     * @var string
     */
    protected $name = 'wjd';

    /**
     * Command Aliases - Helpful when you want to trigger command with more than one name.
     *
     * @var array<string>
     */
    protected $aliases = [
        'quote',
        'wistjedat',
        'wistjedatje',
    ];

    /**
     * The Telegram command description.
     *
     * @var string
     */
    protected $description = 'Stuur een wist-je-datje of quote in.';

    /**
     * Command Argument Pattern.
     *
     * @var string
     */
    protected $pattern = '.+';

    /**
     * Handle the activity.
     */
    public function handle()
    {
        // Send typing status
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        // Check the quote
        $quoteText = trim($this->arguments['custom'] ?? '');

        if (empty($quoteText)) {
            $this->replyWithMessage([
                'text' => $this->formatText(self::REPLY_INVALID),
                'parse_mode' => 'HTML',
            ]);

            return;
        }

        // Get user
        $tgUser = $this->getTelegramUser();
        $user = $this->getUser();

        $cacheToken = sprintf('tg.quotes.rate-limit.%s', $tgUser->id);

        // Reject if rate-limited
        if (! $user && Cache::get($cacheToken) > now()) {
            $this->replyWithMessage([
                'text' => $this->formatText(self::REPLY_GUEST_THROTTLED),
            ]);

            return;
        }

        // Build quote
        $quote = new BotQuote();
        $quote->quote = $quoteText;
        $quote->display_name = trim("{$tgUser->firstName} {$tgUser->lastName}") ?: "#{$tgUser->id}";
        $quote->user_id = optional($user)->id;
        $quote->save();

        // Render guest response
        if (! $user) {
            Cache::put($cacheToken, now()->addDay()->setTime(6, 0));
            $this->replyWithMessage([
                'text' => $this->formatText(self::REPLY_GUEST),
            ]);

            return;
        }

        // Prep a keyboard
        $keyboard = (new Keyboard())->inline();
        $keyboard->row(
            Keyboard::inlineButton([
                'text' => 'Bekijk mijn wist-je-datjes',
                'url' => route('account.quotes'),
            ]),
        );

        // Return message
        $this->replyWithMessage([
            'text' => $this->formatText(self::REPLY_USER),
            'reply_markup' => $keyboard,
        ]);
    }
}
