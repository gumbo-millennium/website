<?php

declare(strict_types=1);

namespace App\Bots\Commands;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

/**
 * @codeCoverageIgnore
 */
class BeerCommand extends Command
{
    private const RATE_LIMIT_BEER = 3;

    private const RATE_LIMIT_ALL = 4;

    private const BEER_CONFIG_TEMPLATE = 'assets/yaml/commands/beer/%s.yaml';

    private const BEER_CONFIG_DEFAULT = 'variants';

    private const BEER_ALTERNATIVES = 'alternatives';

    private const BEER_CONFIG_VARIANTS = [
        ['06-12', '31-12', 'variants-christmas'],
    ];

    private const BEER_ALTERNATIVES_LINE = 'Sorry, %s. Wil je anders %s?';

    private const BEER_LINE = '%s %s als %s %s!';

    /**
     * The name of the Telegram command.
     */
    protected string $name = 'bier';

    /**
     * The Telegram command description.
     */
    protected string $description = 'Bedenkt een goed excuus om bier te drinken';

    /**
     * Command Aliases - Helpful when you want to trigger command with more than one name.
     *
     * @var array<string>
     */
    protected array $aliases = ['beer'];

    /**
     * Handle the activity.
     */
    public function handle()
    {
        // Get TG user
        $tgUser = $this->getTelegramUser();

        // Get user and check member rights
        $user = $this->getUser();
        if (! $this->ensureIsMember($user)) {
            return;
        }

        // Check the rate limit
        $rateLimitKey = "tg.beer:{$tgUser->id}";
        $remaining = RateLimiter::remaining($rateLimitKey, self::RATE_LIMIT_ALL);

        Log::info('Beer command called by telegram user {user} with {remaining} hits remaining.', [
            'user' => $tgUser->id,
            'remaining' => $remaining,
        ]);

        if ($remaining <= 0) {
            $this->replyWithMessage([
                'text' => '⏸ Rate limited',
            ]);

            return;
        }

        // Smash that rate limiter
        RateLimiter::hit($rateLimitKey);

        if ($remaining > (self::RATE_LIMIT_ALL - self::RATE_LIMIT_BEER)) {
            $this->replyWithMessage([
                'text' => "🍻 {$this->buildBeerResponse()}",
            ]);

            return;
        }

        $this->replyWithMessage([
            'text' => "🥤 {$this->buildAlternativeResponse()}",
        ]);
    }

    /**
     * Load and parse Yaml file, cache for an hour.
     */
    private function loadConfigFile(string $file): array
    {
        $path = resource_path(sprintf(self::BEER_CONFIG_TEMPLATE, $file));
        if (! file_exists($path) || ! is_file($path)) {
            throw new RuntimeException('Invalid config file');
        }

        $cacheKey = "beer.file.{$file}";

        return Cache::remember($cacheKey, Date::now()->addHour(), fn () => Yaml::parseFile($path));
    }

    private function buildBeerResponse(): string
    {
        // Get intended file
        $configFile = self::BEER_CONFIG_DEFAULT;
        $nowDate = Date::now()->startOfDay();
        foreach (self::BEER_CONFIG_VARIANTS as [$start, $end, $file]) {
            $startDate = Date::createFromFormat('d-m', $start)->startOfDay();
            $endDate = Date::createFromFormat('d-m', $end)->startOfDay();

            if ($startDate <= $nowDate && $endDate >= $nowDate) {
                $configFile = $file;

                break;
            }
        }

        // Get file from cache, or disk if missing
        $config = $this->loadConfigFile($configFile);

        // Get random lines
        return sprintf(
            self::BEER_LINE,
            Arr::random($config['targets']),
            Arr::random($config['methods']),
            Arr::random($config['adjectives']),
            Arr::random($config['subjects']),
        );
    }

    private function buildAlternativeResponse(): string
    {
        // Get file from cache, or disk if missing
        $config = $this->loadConfigFile(self::BEER_ALTERNATIVES);

        // Format string
        return sprintf(
            self::BEER_ALTERNATIVES_LINE,
            Arr::random($config['rejects']),
            Arr::random($config['alternatives']),
        );
    }
}
