<?php

declare(strict_types=1);

namespace App\Bots\Commands;

use App\Helpers\Arr;
use App\Models\User;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use JsonException;
use Telegram\Bot\Commands\Command as TelegramCommand;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\User as TelegramUser;

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

    public function getReplyGifUrl(string $search): ?InputFile
    {
        if (! $apiKey = Config::get('services.tenor.api-key')) {
            dump('no key');

            return null;
        }

        /** @var GuzzleClient $http */
        $http = App::make(GuzzleClient::class);

        $searchUrl = Uri::withQueryValues(new Uri('https://g.tenor.com/v1/search'), [
            'key' => $apiKey,
            'q' => $search,
            'locale' => 'nl_NL',
            'contentfilter' => 'medium',
            'media_filter' => 'minimal',
            'limit' => 15,
        ]);

        $result = $http->get($searchUrl);

        try {
            $body = json_decode($result->getBody()->getContents(), true, 64, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $body = null;
        }

        if ($result->getStatusCode() !== 200 || ! $body) {
            return null;
        }

        // Pick a random image from the results
        $availableImages = Arr::get($body, 'results', []);
        $chosenImage = Arr::random($availableImages);

        $imageId = Arr::get($chosenImage, 'id');
        $imagePublicUrl = Arr::get($chosenImage, 'url');
        $imageUrl = Arr::get($chosenImage, 'media.0.mp4.url');

        if (! $imageId || ! filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            return null;
        }

        $shareUrl = Uri::withQueryValues(new Uri('https://g.tenor.com/v1/registershare'), [
            'key' => $apiKey,
            'id' => $imageId,
            'locale' => 'nl_NL',
            'q' => $search,
        ]);

        $http->get($shareUrl);

        return InputFile::create($imageUrl, basename($imagePublicUrl));
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

    protected function forgetRateLimit(string $key): void
    {
        Cache::forget($this->getRateLimitKey($key));
    }

    protected function rateLimit(string $key, string $message = '⏸ Nog even wachten...', string $period = 'PT5M'): bool
    {
        // Rate limit
        $cacheKey = $this->getRateLimitKey($key);

        if (Cache::get($cacheKey) > Date::now()) {
            $this->replyWithMessage([
                'text' => $message,
            ]);

            return true;
        }

        // Prep rate limit
        $next = Date::now()->toImmutable()->add($period);
        Cache::put($cacheKey, $next, $next->addWeek());

        return false;
    }

    private function getRateLimitKey(string $key): string
    {
        return sprintf('tg.rate-limits.%s.%s', $key, optional($this->getTelegramUser())->id ?? 'shared');
    }
}
