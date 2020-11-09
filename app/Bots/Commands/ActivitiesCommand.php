<?php

declare(strict_types=1);

namespace App\Bots\Commands;

use App\Models\Activity;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Actions;

class ActivitiesCommand extends Command
{
    private const USER_MESSAGE = <<<'MARKDOWN'
    Activiteiten 🎯

    %s

    Voor meer info, check <a href="%s">de site</a> of het <a
    href="https://t.me/joinchat/AAAAAFOnLOIW0Tt-Ag-eKA">activiteitenkanaal</a>.

    %s
    MARKDOWN;

    /**
     * Message shown for users to login with
     */
    private const REGISTER_MESSAGE = <<<'TEXT'
    Je bent niet ingelogd op de bot, log in door een prive-bericht met '/login' te sturen.
    TEXT;


    /**
     * The name of the Telegram command.
     * @var string
     */
    protected $name = 'activities';

    /**
     * The Telegram command description.
     * @var string
     */
    protected $description = 'Toont de activiteiten';

    /**
     * Handle the activity
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
                '%s | <a href="%s>%s</a>',
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
                $suffixes[] = $activity->price_label;
            }

            // Add available seats
            if ($activity->enrollments->pluck('user_id')->contains($userId)) {
                $suffixes[] = 'ingeschreven';
            } elseif ($activity->seats > 0) {
                $suffixes[] = sprintf(
                    '%d van %d plekken beschikbaar',
                    $activity->available_seats,
                    $activity->seats
                );
            }

            // Merge into brackets
            if (!empty($suffixes)) {
                $line = sprintf('%s (%s)', $line, \implode(', ', $suffixes));
            }

            // Add line
            $lines[] = $line;
        }

        // Message
        $message = trim(sprintf(
            self::USER_MESSAGE,
            implode("  \n", $lines),
            route('activity.index'),
            $user === null ? self::REGISTER_MESSAGE : null
        ));

        // Add debug
        Log::info('Build string {message}', compact('message', 'lines'));

        // Return message
        $this->replyWithMessage([
            'text' => $message,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ]);
    }
}
