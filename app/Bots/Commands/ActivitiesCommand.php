<?php

declare(strict_types=1);

namespace App\Bots\Commands;

use App\Models\Activity;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Actions;
use Telegram\Bot\Keyboard\Keyboard;

/**
 * @codeCoverageIgnore
 */
class ActivitiesCommand extends Command
{
    private const ACTIVITY_URL = 'https://gumbo.nu/acpublicaties';

    /**
     * Message shown for users to login with.
     */
    private const REGISTER_MESSAGE = <<<'TEXT'
        Je bent niet ingelogd op de bot, stuur in een PM /login om in te loggen.
        TEXT;

    private const USER_MESSAGE = <<<'MARKDOWN'
        Activiteiten 🎯

        %s

        Voor meer info, check de site of het activiteitenkanaal.

        %s
        MARKDOWN;

    /**
     * The name of the Telegram command.
     */
    protected string $name = 'activiteiten';

    /**
     * The Telegram command description.
     */
    protected string $description = 'Toon de (besloten) activiteiten';

    /**
     * Handle the activity.
     */
    public function handle()
    {
        // Send typing status
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        // Get user
        $user = $this->getUser();
        $userId = \optional($user)->id;

        // Get activities
        $activities = Activity::getNextActivities($user)
            ->with(['enrollments:id,user_id'])
            ->take(5)
            ->get();

        // Prep lines
        $lines = [];

        // Loop activities
        foreach ($activities as $activity) {
            // Sanity
            \assert($activity instanceof Activity);

            // Prep name
            $activityName = \htmlspecialchars($activity->name);
            if ($activity->is_cancelled) {
                $activityName = "<del>{$activityName}</del>";
            }

            // Prep a default line
            $line = sprintf(
                '%s | <a href="%s">%s</a>',
                $activity->start_date->isoFormat('DD-MM'),
                route('activity.show', compact('activity')),
                $activityName,
            );

            // Activity is cancelled
            if ($activity->is_cancelled) {
                $lines[] = "{$line} (geannuleerd)";

                continue;
            }

            // Activity is sold out
            if ($activity->available_seats === 0) {
                $lines[] = "{$line} (uitverkocht)";

                continue;
            }

            // Add price
            $suffixes = [];
            if ($activity->price > 0) {
                $suffixes[] = $activity->price_range;
            }

            // Add available seats
            if ($activity->enrollments->pluck('user_id')->contains($userId)) {
                $suffixes[] = 'ingeschreven';
            } elseif ($activity->seats > 0) {
                $suffixes[] = sprintf(
                    '%d / %d beschikbaar',
                    $activity->available_seats,
                    $activity->seats,
                );
            }

            // Merge into brackets
            if (! empty($suffixes)) {
                $line = sprintf('%s (%s)', $line, \implode(', ', $suffixes));
            }

            // Add line
            $lines[] = $line;
        }

        // Message
        $message = trim(sprintf(
            self::USER_MESSAGE,
            implode("  \n", $lines),
            $user === null ? self::REGISTER_MESSAGE : null,
        ));

        // Add debug
        Log::info('Build string {message}', compact('message', 'lines'));

        // Prep a keyboard
        $keyboard = (new Keyboard())->inline();
        $keyboard->row([
            Keyboard::inlineButton([
                'text' => 'Check de site',
                'url' => route('activity.index'),
            ]),
            Keyboard::inlineButton([
                'text' => 'Activiteitenkanaal',
                'url' => $this->getActivityChannelUrl(),
            ]),
        ]);

        // Return message
        $this->replyWithMessage([
            'text' => $message,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'reply_markup' => $keyboard,
        ]);
    }

    /**
     * Retursn the effective URL to the activity channel, or the URL as-is.
     */
    private function getActivityChannelUrl(): string
    {
        // Get URL from cache
        $effectiveUrl = Cache::get('telegram.bot.activities.url');
        if ($effectiveUrl) {
            return $effectiveUrl;
        }

        // Get HTTP driver
        $http = App::make(GuzzleClient::class);
        \assert($http instanceof GuzzleClient);

        // Get URL
        $response = $http->get(self::ACTIVITY_URL, [
            'on_stats' => static function (TransferStats $stats) use (&$effectiveUrl) {
                $effectiveUrl = (string) $stats->getEffectiveUri();
            },
        ]);

        // Fail if not valid
        if ($response->getStatusCode() !== 200) {
            return self::ACTIVITY_URL;
        }

        // Cache and return
        Cache::put('telegram.bot.activities.url', $effectiveUrl, now()->addDay());

        return $effectiveUrl;
    }
}
