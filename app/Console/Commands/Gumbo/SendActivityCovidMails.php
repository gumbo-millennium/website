<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo;

use App\Mail\ActivityCovidMail;
use App\Models\Activity;
use App\Models\ScheduledMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Console\Output\OutputInterface;

class SendActivityCovidMails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gumbo:send-activity-covid-mails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends all covid mails that are due';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get all activities that take place soon
        $activities = Activity::query()
            ->whereBetween('start_date', [now()->addHours(6), now()->addHours(26)])
            ->whereNull('cancelled_at')
            ->where('location_type', '!=', Activity::LOCATION_ONLINE)
            ->get();

        // Loop
        foreach ($activities as $activity) {
            // Check it
            $this->line(
                "Sending Covid mail for <comment>{$activity->name}</>.",
                null,
                OutputInterface::VERBOSITY_VERBOSE
            );

            // Get mail
            $scheduled = ScheduledMail::findForModelMail($activity, 'covid-mail');
            if ($scheduled->is_sent) {
                $this->line("Mail for <comment>{$activity->name}</> already sent");

                continue;
            }

            // Send it now
            $scheduled->scheduled_for = now();
            $scheduled->save();

            // Report
            $this->line('Schedule entry created', null, OutputInterface::VERBOSITY_VERBOSE);

            // Send
            $this->sendMail($activity);

            // Done
            $scheduled->sent_at = now();
            $scheduled->save();

            // Done
            $this->info("Mails for <comment>{$activity->name}</> sent");
        }
    }

    private function sendMail(Activity $activity): void
    {
        // Get all enrollments
        $enrollments = $activity->enrollments()
            ->with('user')
            ->get();

        // Iterate
        foreach ($enrollments as $enrollment) {
            $user = $enrollment->user;
            $this->line(
                "Sending to <info>{$user->name}</> (<comment>{$user->id}</>)",
                null,
                OutputInterface::VERBOSITY_VERY_VERBOSE
            );

            Mail::to($enrollment->user)
                ->queue(new ActivityCovidMail($enrollment));
        }
    }
}
