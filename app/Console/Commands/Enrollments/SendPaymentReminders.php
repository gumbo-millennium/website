<?php

declare(strict_types=1);

namespace App\Console\Commands\Enrollments;

use App\Models\Enrollment;
use App\Models\States\Enrollment\Confirmed;
use App\Notifications\Activities\UnpaidEnrollmentReminder;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;

class SendPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollment:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send all users that have unpaid enrollments a reminder';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $targets = Enrollment::query()
            ->whereState('state', Confirmed::class)
            ->whereNotNull('price')
            ->with(['user', 'activity'])
            ->get();

        $reminderCount = 0;
        /** @var Enrollment */
        foreach ($targets as $target) {
            $target->user->notify(new UnpaidEnrollmentReminder($target));

            $reminderCount++;
            $this->line("Sent reminder to <info>{$target->user->name}</> for <info>{$target->activity->name}</> (#{$target->id})", null, OutputInterface::VERBOSITY_VERBOSE);
        }

        $this->info(sprintf('Sent out %d reminders', $reminderCount));

        return 0;
    }
}
