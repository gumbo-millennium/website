<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo;

use App\Mail\ActivityCovidMail;
use App\Models\Activity;
use App\Models\ScheduledMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendActivityCovidMails extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'gumbo:send-activity-covid-mails';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Sends all covid mails that are due';

    /**
     * Create a new command instance.
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @return mixed
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
            $this->line("Checking {$activity->name}");

            // Get mail
            $scheduled = ScheduledMail::findForModelMail($activity, 'covid-mail');
            if ($scheduled->is_sent) {
                $this->line("Mail already sent");
                continue;
            }

            // Send it now
            $scheduled->scheduled_for = now();
            $scheduled->save();

            // Report
            $this->line("Mail schedule created");

            // Send
            $this->sendMail($activity);

            // Done
            $scheduled->sent_at = now();
            $scheduled->save();

            // Done
            $this->info("Mails sent");
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
            Mail::to($enrollment->user)
                ->send(new ActivityCovidMail($enrollment));
        }
    }
}
