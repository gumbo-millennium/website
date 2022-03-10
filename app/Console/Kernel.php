<?php

declare(strict_types=1);

namespace App\Console;

use App\Jobs\CleanExpiredExportsJob;
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
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Expunge outdated non-critical data daily
        $schedule->command('avg:flush')->daily();

        // Wipe old Telescope records
        $schedule->command('telescope:prune')->daily();

        // Wipe old data
        $schedule->command('model:prune')->weeklyOn(6, '22:00');

        // Wipe old exports
        $schedule->job(CleanExpiredExportsJob::class)->weekly();

        // Update enrollments' user_type every hour
        $schedule->job(UpdateEnrollmentUserTypes::class)->dailyAt('04:00');

        // Update users from API every night
        $schedule->command('gumbo:update-user')->dailyAt('03:00');

        // Update users from API every night
        $schedule->command('gumbo:update-groups --missing')->dailyAt('03:30');

        // Update Sponsor logos nightly (in case some were missed)
        $schedule->command('gumbo:update-sponsor-logos')->dailyAt('05:00');

        // Update shop twice a day
        $schedule->command('shop:update')->twiceDaily(11, 23);

        // Send feature mails on common times
        $schedule->command('gumbo:send-activity-feature-mails')->cron('15 2,7,12,16,21 * * *');

        // Updated maillists every other night
        $schedule->job(ConstructGoogleActionList::class)->days(1, 3, 5)->dailyAt('06:00');

        // Clean enrollments hourly
        $schedule->job(PruneExpiredEnrollments::class)->hourlyAt(55);

        // Weekly make a backup of the images
        $schedule->command('app:backup-images')->weeklyOn(0, '06:30');

        // Send quotes weekly
        $schedule->job(SendBotQuotes::class)->weeklyOn(1, '08:15');
        $schedule->command('bot:update')->hourly();

        // Manually check payments every 30 minutes
        $schedule->command('payments:update', ['--all'])->everyThirtyMinutes();

        // Shop expiration and reminders
        $schedule->command('shop:send-reminders')->hourlyAt(20);
        $schedule->command('shop:cancel-expired')->twiceDaily(2, 14);

        // Clear expired data exports
        $schedule->command('gumbo:avg:prune-exports')->weekly();

        // Clear expired webcam images
        $schedule->command('gumbo:prune-webcams')->hourly();
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
