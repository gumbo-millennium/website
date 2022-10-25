<?php

declare(strict_types=1);

namespace App\Console\Commands\Enrollments;

use App\Models\Enrollment;
use App\Models\States\Enrollment as States;
use Illuminate\Console\Command;

class PushEnrollmentStates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollment:push-states';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all enrollment states';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = Enrollment::query()
            ->whereState('state', [
                States\Created::class,
                States\Seeded::class,
                States\Confirmed::class,
            ])
            ->with(['activity'])
            ->cursor();

        foreach ($query as $enrollment) {
            $changed = false;

            $activity = $enrollment->activity;
            if ($enrollment->state instanceof States\Created && ! $activity->form) {
                $enrollment->state->transitionTo(States\Seeded::class);
                $changed = true;
            }

            if ($enrollment->state instanceof States\Seeded && $enrollment->price === null) {
                $enrollment->state->transitionTo(States\Confirmed::class);
                $changed = true;
            }

            if (! $changed) {
                continue;
            }

            $this->line("Updating <info>{$enrollment->id}</> ({$enrollment->user->name}, {$enrollment->activity->name})");

            $enrollment->save();
        }
    }
}
