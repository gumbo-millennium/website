<?php

declare(strict_types=1);

namespace App\Console;

use App\Jobs\Mail\ConstructGoogleActionList;
use App\Jobs\PruneExpiredEnrollments;
use App\Jobs\SendBotQuotes;
use App\Jobs\UpdateEnrollmentUserTypes;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Expunge outdated non-critical data daily
        $schedule->command('avg:flush')->daily();

        // Wipe old Telescope records
        $schedule->command('telescope:prune')->daily();

        // Update enrollments' user_type every hour
        $schedule->job(UpdateEnrollmentUserTypes::class)->dailyAt('04:00');

        // Update users from API every night
        $schedule->command('gumbo:update-user')->dailyAt('03:00');

        // Update users from API every night
        $schedule->command('gumbo:update-groups --missing')->dailyAt('03:30');

        // Update Sponsor logos nightly (in case some were missed)
        $schedule->command('gumbo:update-sponsor-logos')->dailyAt('05:00');

        // Update shop twice a day
        $schedule->command('gumbo:update-shop')->twiceDaily(11, 23);

        // Send required mails every hour
        // $schedule->command('gumbo:send-activity-covid-mails')->hourly();

        // Updated maillists every other night
        $schedule->job(ConstructGoogleActionList::class)->days(1, 3, 5)->dailyAt('06:00');

        // Clean enrollments hourly
        $schedule->job(PruneExpiredEnrollments::class)->hourlyAt(55);

        // Weekly make a backup of the images
        $schedule->command('app:backup-images')->weeklyOn(0, '06:30');

        // Send quotes weekly
        $schedule->job(SendBotQuotes::class)->weeklyOn(1, '08:15');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
    }
}
