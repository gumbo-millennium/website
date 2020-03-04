<?php

declare(strict_types=1);

namespace App\Console;

use App\Jobs\PruneExpiredEnrollments;
use App\Jobs\UpdateEnrollmentUserTypes;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
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

        // Clean enrollments hourly
        $schedule->job(PruneExpiredEnrollments::class)->hourlyAt(55);
    }

    /**
     * Register the commands for the application.
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
