<?php

declare(strict_types=1);

namespace App\Bots\Commands;

use App\Models\BotQuote;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Telegram\Bot\Actions;
use Telegram\Bot\Keyboard\Keyboard;

/**
 * @codeCoverageIgnore
 */
class QuoteCommand extends Command
{
    private const REPLY_TO_SHORT = <<<'MSG'
        Geef wist-je-datje 😠

        <code>/wjd [bericht]</code>
        MSG;

    private const MAX_QUOTE_LENGTH = 210;

    private const REPLY_TOO_LONG = <<<'MSG'
        🤌 Moet Korter 🤌

        Je verhaal is te lang, maak het maar wat mand.
        MSG;

    private const REPLY_GUEST_THROTTLED = <<<'MSG'
        ⏰ Sorry, je mag nog geen wist-je-datje insturen.

        Log in via /login om deze beperking weg te halen.
        MSG;

    private const REPLY_OK = <<<'MSG'
        Bedankt voor het insturen van dit pareltje:

        <blockquote>%s</blockquote>

        📬 Maandag gaat 'ie naar de Gumbode.
        MSG;

    private const REPLY_PUBLIC = <<<'MSG'
        Hey, <a href="tg://user?id=%s">%s</a>, wil je volgende keer geniepig doen?
        Stuur je wist-je-datje dan in een privébericht.
        MSG;

    private const REPLY_GUEST = <<<'MSG'
        Je bent niet ingelogd, dus je kan maximaal 1 wist-je-datje per
        dag insturen. Login via /login om deze limiet te verwijderen.
        MSG;

    /**
     * The name of the Telegram command.
     */
    protected string $name = 'wjd';

    /**
     * Command Aliases - Helpful when you want to trigger command with more than one name.
     *
     * @var array<string>
     */
    protected array $aliases = [
        'quote',
        'wistjedat',
        'wistjedatje',
    ];

    /**
     * The Telegram command description.
     */
    protected string $description = 'Stuur een wist-je-datje of quote in.';

    /**
     * Command Argument Pattern.
     */
    protected string $pattern = '.+';

    /**
     * Handle the activity.
     */
    public function handle()
    {
        if ($this->update->message == null) {
            return;
        }

        // Check the quote, remove the @Username if found
        $quoteText = $this->getCommandBody();

        //check if quote is unique
        $messageId = $this->update->message->message_id;
        if (BotQuote::where('message_id', $messageId)->exists()) {
            return;
        }

        // Send typing status
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        Log::info('Derrived quote {quote} from {message}.', [
            'quote' => $quoteText,
            'message' => $this->getUpdate()->getMessage()->getText(),
        ]);

        if (empty($quoteText)) {
            $this->replyWithMessage([
                'text' => $this->formatText(self::REPLY_TO_SHORT),
                'parse_mode' => 'HTML',
            ]);

            return;
        }

        // Get user
        $tgUser = $this->getTelegramUser();
        $user = $this->getUser();

        if (Str::length($quoteText) > self::MAX_QUOTE_LENGTH) {
            Log::warning('Sender {user} sent overly long quote of {length} characters {quote}', [
                'user' => $user ?? $tgUser,
                'length' => Str::length($quoteText),
                'quote' => Str::limit($quoteText, 500),
            ]);

            $this->replyWithMessage([
                'text' => $this->formatText(self::REPLY_TOO_LONG),
                'parse_mode' => 'HTML',
            ]);

            return;
        }

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
        $quote->user_id = $user?->id;
        $quote->message_id = $messageId;
        $quote->save();

        $preparedMessage = $this->formatText(self::REPLY_OK, e($quoteText));

        if ($this->isInGroupChat()) {
            $preparedMessage .= PHP_EOL . PHP_EOL . $this->formatText(self::REPLY_PUBLIC, $tgUser->id, e($quote->username ?? $quote->display_name));
        }

        // Send single user message
        if ($user) {
            $keyboard = (new Keyboard())->inline();
            $keyboard->row([
                Keyboard::inlineButton([
                    'text' => 'Bekijk mijn wist-je-datjes',
                    'url' => route('account.quotes'),
                ]),
            ]);

            $this->replyWithMessage([
                'text' => $preparedMessage,
                'reply_to_message_id' => $this->getUpdate()->getMessage()->getMessageId(),
                'reply_markup' => $keyboard,
                'parse_mode' => 'HTML',
            ]);

            return;
        }

        // Apply rate limit
        Cache::put($cacheToken, now()->addDay()->setTime(6, 0));

        // Send messages
        $this->replyWithMessage([
            'text' => $preparedMessage,
            'reply_to_message_id' => $this->getUpdate()->getMessage()->getMessageId(),
        ]);

        // Render guest response
        $this->replyWithMessage([
            'text' => $this->formatText(self::REPLY_GUEST),
            'disable_notification' => true,
        ]);
    }
}
