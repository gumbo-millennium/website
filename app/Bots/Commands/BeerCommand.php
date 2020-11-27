<?php

declare(strict_types=1);

namespace App\Bots\Commands;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class BeerCommand extends Command
{
    private const BEER_CONFIG_FILE = 'assets/yaml/beer-command.yaml';

    /**
     * The name of the Telegram command.
     * @var string
     */
    protected $name = 'bier';

    /**
     * The Telegram command description.
     * @var string
     */
    protected $description = 'Bedenkt een goed excuus om bier te drinken';

    /**
     * Command Aliases - Helpful when you want to trigger command with more than one name.
     * @var array<string>
     */
    protected $aliases = ['beer'];

    /**
     * Handle the activity
     */
    public function handle()
    {
        // Get TG user
        $tgUser = $this->getTelegramUser();

        // Rate limit
        $cacheKey = sprintf('tg.beer.%s', $tgUser->id);
        if (Cache::get($cacheKey) > now()) {
            $this->replyWithMessage([
                'text' => '⏸ Rate limited (1x per min)'
            ]);
            return;
        }

        // Prep rate limit
        Cache::put($cacheKey, now()->addMinute(), now()->addMinutes(5));

        // Get user and check member rights
        $user = $this->getUser();
        if (!$this->ensureIsMember($user)) {
            return;
        }

        // Get config
        $configPath = resource_path(self::BEER_CONFIG_FILE);
        if (!file_exists($configPath) || !is_file($configPath)) {
            $this->replyWithMessage([
                'text' => 'Dit commando is helaas kapot 😢'
            ]);
            return;
        }

        // Get config
        try {
            $config = Yaml::parseFile($configPath);
        } catch (ParseException $e) {
            $this->replyWithMessage([
                'text' => 'Dit commando is helaas kapot 😢'
            ]);
            return;
        }

        // Get random lines
        $format = sprintf(
            '%s %s als %s %s!',
            Arr::random($config['targets']),
            Arr::random($config['methods']),
            Arr::random($config['adjectives']),
            Arr::random($config['subjects'])
        );

        // Send as-is
        $this->replyWithMessage([
            'text' => "🍻 $format"
        ]);
    }
}
