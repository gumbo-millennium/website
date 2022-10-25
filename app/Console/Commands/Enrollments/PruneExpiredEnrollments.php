<?php

declare(strict_types=1);

namespace App\Console\Commands\Enrollments;

use App\Enums\EnrollmentCancellationReason;
use App\Enums\PaymentStatus;
use App\Facades\Payments;
use App\Models\Enrollment;
use App\Models\States\Enrollment as States;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;
use Symfony\Component\Console\Output\OutputInterface;

class PruneExpiredEnrollments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollment:prune';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actually expire enrollments that have expired.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = Enrollment::query()
            ->whereState('state', [
                States\Created::class,
                States\Seeded::class,
            ])
            ->where('expire', '<', Date::now()->subMinutes(5))
            ->with(['activity', 'payments'])
            ->cursor();

        /** @var \App\Models\Enrollment $enrollment */
        foreach ($query as $enrollment) {
            $hasPaidPayment = false;

            /** @var \App\Models\Payment $payment */
            foreach ($enrollment->payments as $payment) {
                if ($payment->status === PaymentStatus::PAID) {
                    $hasPaidPayment = true;

                    break;
                }
            }

            if ($hasPaidPayment) {
                $this->line("Skipping paid enrollment <info>{$enrollment->id}</>", null, OutputInterface::VERBOSITY_VERBOSE);

                continue;
            }

            /** @var \App\Models\Payment $payment */
            foreach ($enrollment->payments as $payment) {
                if ($payment->status !== PaymentStatus::OPEN) {
                    continue;
                }

                $this->line("Cancelling open payment <info>{$payment->id}</>", null, OutputInterface::VERBOSITY_VERBOSE);
                Payments::find($payment->provider)->cancel($payment);
            }

            $enrollment->deleted_reason = EnrollmentCancellationReason::TIMEOUT;
            $enrollment->save();

            $enrollment->state->transitionTo(States\Cancelled::class);
            $enrollment->save();

            $this->line("Expired <info>{$enrollment->id}</> ({$enrollment->user->name}, {$enrollment->activity->name})");
        }

        return 0;
    }
}
