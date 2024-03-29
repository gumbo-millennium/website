<?php

declare(strict_types=1);

namespace App\Console;

use App\Jobs\SendBotQuotes;
use App\Jobs\UpdateEnrollmentUserTypes;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Config;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * HEADS UP
     * Never schedule stuff between 02:00 - 03:00 due to Daylight Saving Time.
     * Events might not run in that period.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Backups
        $schedule->command('backup:create')->daily()->at('01:00');
        $schedule->command('backup:create', ['--full' => true])->weekly()->at('01:30');

        // Expunge data past it's retention period
        $schedule->command('avg:prune-enrollment-data')->twiceMonthly();
        $schedule->command('telescope:prune')->daily();
        $schedule->command('model:prune')->weeklyOn(6, '22:00');

        // Ensure Google Wallet data is updated, if enabled
        if (Config::get('services.google.wallet.enabled', false)) {
            $schedule->command('google-wallet:prune-nonces')->daily();
            $schedule->command('google-wallet:create-missing-activities')->twiceDailyAt(offset: 10);
            $schedule->command('google-wallet:activity', ['--with-enrollments'])->twiceDailyAt(offset: 20);
        }

        // Wipe old enrollment exports
        $schedule->command('enrollment:prune-exports')->daily();

        // Wipe old Personal Access Tokens
        $schedule->command('sanctum:prune-expired --hours=24')->daily();

        // Update enrollments' user_type every day
        $schedule->job(UpdateEnrollmentUserTypes::class)->daily();

        // Update users from API every night
        $schedule->command('gumbo:user:update')->dailyAt('03:00');

        // Update groups from API every night
        $schedule->command('gumbo:update-groups --missing')->dailyAt('03:30');

        // Update Sponsor logos nightly (in case some were missed)
        $schedule->command('gumbo:update-sponsor-logos')->dailyAt('05:00');

        // Update shop twice a day
        $schedule->command('shop:update')->twiceDaily(11, 23);

        // Send feature mails on common times
        $schedule->command('gumbo:send-activity-feature-mails')->cron('15 2,7,12,16,21 * * *');
        $schedule->command('gumbo:activity:send-messages')->everyFiveMinutes();
        $schedule->command('gumbo:activity:send-system-messages', ['--send'])->hourlyAt(10);

        // Updated maillists every morning
        $schedule->command('gumbo:update-lists')->dailyAt('06:00');

        // Clean enrollments hourly
        $schedule->command('enrollment:prune')->hourlyAt(55);

        // Send payment reminders on fridays at 20:30
        $schedule->command('enrollment:send-reminders')->fridays()->at('20:30');

        // Update ticket PDFs from enrollments hourly
        $schedule->command('gumbo:update-ticket-pdfs')->hourly();

        // Weekly make a backup of the images
        $schedule->command('app:backup-images')->weeklyOn(0, '06:30');

        // Send quotes weekly, if Telegram bot is configured
        if (Config::get('telegram.bots.gumbot.token')) {
            $schedule->job(SendBotQuotes::class)->weeklyOn(1, '08:15');
            $schedule->command('bot:update')->hourly();
        }

        // Manually check payments every 30 minutes
        $schedule->command('payments:update', ['--all'])->everyThirtyMinutes();

        // Shop expiration and reminders
        $schedule->command('shop:send-reminders')->hourlyAt(20);
        $schedule->command('shop:cancel-expired')->twiceDaily(2, 14);

        // Clear expired data exports
        $schedule->command('gumbo:avg:prune-exports')->weekly();

        // Clear expired webcam images
        $schedule->command('gumbo:prune-webcams')->hourly();

        // Remove expired filepond items every four hours, and remove orphanned Photos every day
        $schedule->command('gumbo:prune-gallery-filepond')->hourly();
        $schedule->command('gumbo:prune-gallery-filepond', ['--prune', '--clean'])->dailyAt('03:14');

        // Auto-remove old Glide images
        $schedule->command('gumbo:prune-glide-cache')->daily();

        // Update Telegram Gifs every now and then
        $schedule->command('tenor:preload-gifs', ['--prune'])->thursdays()->at('03:33');

        // Update the Mollie settlements every night, when an org key is set
        if (Config::get('mollie.org_key')) {
            $schedule->command('payments:settlements')->dailyAt('06:00');
        }
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
