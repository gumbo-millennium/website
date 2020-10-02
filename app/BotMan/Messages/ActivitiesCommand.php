<?php

declare(strict_types=1);

namespace App\BotMan\Messages;

use App\Models\Activity;
use App\Models\User;
use BotMan\BotMan\BotMan;

class ActivitiesCommand extends AbstractMessage
{
    private const USER_MESSAGE = <<<'MARKDOWN'
    Activiteiten 🎯

    %s

    Voor meer info, check <a href="%s">de site</a> of het <a
    href="https://t.me/joinchat/AAAAAFOnLOIW0Tt-Ag-eKA">activiteitenkanaal</a>.
    MARKDOWN;

    /**
     * Returns all activities planned, optionally for the given user
     * @param BotMan $bot
     * @param null|User $user
     * @return void
     * @throws InvalidArgumentException
     */
    public function run(BotMan $bot, ?User $user): void
    {
        // Send image notification
        $bot->types();

        // Get activities
        $activities = Activity::getNextActivities($user)
            ->with(['enrollments:id'])
            ->take(5)
            ->get();

        // Prep lines
        $lines = [];

        foreach ($activities as $activity) {
            // Sanity
            \assert($activity instanceof Activity);

            // Prep a default line
            $line = sprintf(
                '%s | <a href="%s">%s</a>',
                $activity->start_date->isoFormat('DD-MM'),
                route('activity.show', compact('activity')),
                $activity->name,
            );

            if ($activity->is_cancelled) {
                $line .= " (GEANNULEERD)";
            } elseif ($activity->available_seats > 0 && $activity->price > 0) {
                $line .= sprintf(
                    ' (%s, %s / %s %s)',
                    $activity->price_label,
                    $activity->available_seats,
                    $activity->seats,
                    $activity->available_seats === 1 ? 'plek' : 'plekken'
                );
            } elseif ($activity->available_seats === 0) {
                $line .= " (uitverkocht)";
            }


            // Add line
            $lines[] = $line;
        }

        // Message
        $message = sprintf(
            self::USER_MESSAGE,
            implode("  \n", $lines),
            route('activity.index')
        );

        // Add debug
        logger()->info('Build string {message}', compact('message', 'lines'));

        // Return message
        $bot->reply($message);
    }
}
