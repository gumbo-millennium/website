<?php

declare(strict_types=1);

namespace App\Bots\Commands;

use App\Events\Tenor\GifSharedEvent;
use App\Helpers\Str;
use App\Models\User;
use App\Services\TenorGifService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;
use Telegram\Bot\Commands\Command as TelegramCommand;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\User as TelegramUser;

/**
 * @codeCoverageIgnore
 */
abstract class Command extends TelegramCommand
{
    /**
     * Runs a string through sprintf, and unwraps single newlines.
     */
    public function formatText(string $text, ...$args): string
    {
        $out = sprintf($text, ...$args);

        return preg_replace('/(?<!\n)\n(?=\S)/', ' ', $out);
    }

    /**
     * Sends a random, pre-cached reply gif.
     * @return null|InputFile|string URL to the selected GIF, or null if none were available
     */
    public function getReplyGifUrl(string $group): null|string|InputFile
    {
        // Search group is invalid
        if (Str::slug($group) !== $group) {
            return null;
        }

        /** @var TenorGifService */
        $service = app(TenorGifService::class);

        if (! $service->getApiKey()) {
            return null;
        }

        try {
            $gif = $service->getGifPathFromGroup($group);

            $result = App::isLocal()
                ? InputFile::createFromContents($service->getDisk()->get($gif), "{$group}.gif")
                : $service->getDisk()->url($gif);

            GifSharedEvent::dispatch(basename('.gif'), $group);

            return $result;
        } catch (RuntimeException) {
            return null;
        }
    }

    protected function isInGroupChat(): bool
    {
        return $this->getTelegramUser()->id !== $this->update->getChat()->id;
    }

    /**
     * Returns Telegram User.
     */
    protected function getTelegramUser(): ?TelegramUser
    {
        // Look for a message
        $message = $this->update->getMessage();
        if (! $message || ! $message instanceof Message) {
            return null;
        }

        // Look for a user
        $chatUser = $message->from;
        if (! $chatUser || ! $chatUser instanceof TelegramUser) {
            return null;
        }

        // Return user
        return $chatUser;
    }

    /**
     * Get the user based on the update.
     */
    protected function getUser(): ?User
    {
        $chatUser = $this->getTelegramUser();

        // Skip if empty
        if (! $chatUser) {
            return null;
        }

        // Find the user that has this telegram ID
        return User::query()
            ->whereTelegramId((string) $chatUser->id)
            ->first();
    }

    /**
     * Require the user to be logged in and a member.
     */
    protected function ensureIsMember(?User $user): bool
    {
        $message = null;
        if (! $user) {
            $message = <<<'EOL'
            🛂 Je moet ingelogd zijn om dit commando te gebruiken.

            Log in door /login in een PM te sturen.
            EOL;
        } elseif (! $user->is_member) {
            $message = <<<'EOL'
            ⛔ Dit commando is alleen voor leden.
            EOL;
        }

        // Pass
        if (! $message) {
            return true;
        }

        // Reply with the error
        $this->replyWithMessage([
            'text' => $message,
        ]);

        return false;
    }

    protected function getRateLimitKey(string $key): string
    {
        // Prefer the user ID, but fall back to the chat ID
        $user = $this->update->message?->from?->id ?? $this->update->message?->chat?->id ?? 'shared';

        // Combine all stuff to form the key
        return "tg:{$key}:{$user}";
    }

    protected function forgetRateLimit(string $key): void
    {
        RateLimiter::resetAttempts($this->getRateLimitKey($key));
    }

    /**
     * Check if the user has hit a rate limit. This is on a per-Telegram-user basis.
     * @param string $key Key to use to uniquely identify the rate limit
     * @param string $message Message to display
     * @param string $decay Decay time (in ISO 8601 format)
     * @return bool True if the user has hit the rate limit
     */
    protected function rateLimit(string $key, string $message = '⏸ Nog even wachten...', string $decay = 'PT5M'): bool
    {
        // Use Laravel rate limiter
        $fullKey = $this->getRateLimitKey($key);
        if (RateLimiter::tooManyAttempts($fullKey, 1)) {
            $this->replyWithMessage([
                'text' => $message,
            ]);

            return true;
        }

        // Tap the rate limiter
        RateLimiter::hit($fullKey, Date::now()->add($decay)->diffInSeconds());

        return false;
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
