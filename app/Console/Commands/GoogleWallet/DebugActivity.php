<?php

declare(strict_types=1);

namespace App\Console\Commands\GoogleWallet;

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\GoogleWallet\EventClass;
use App\Models\GoogleWallet\EventObject;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DebugActivity extends GoogleWalletCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
        google-wallet:debug-activity
            {activity : Activity to list, slug or ID}
            {--all : List all activities, exclusive with --enrollments}
            {--enrollments : List enrollments too, exclusive with --all}
        CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists the Google Wallet Class information of an activity';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check args
        if ($this->option('all') && $this->option('enrollments')) {
            $this->error('You cannot use --all and --enrollments together');

            return self::FAILURE;
        }

        // Find activity
        $activityQuery = Activity::query()
            ->withoutGlobalScopes()
            ->if($this->option('enrollments'), fn ($query) => $query->with('enrollments'))
            ->unless($this->option('all'), fn ($query) => $query->orWhere([
                ['id', '=', $this->argument('activity')],
                ['slug', '=', $this->argument('activity')],
            ])->limit(1))
            ->orderBy('id');

        if ($activityQuery->count() === 0 && ! $this->option('all')) {
            $this->error('Activity not found');

            return self::FAILURE;
        }

        $tableRows = [];

        foreach ($activityQuery->lazy(10) as $activity) {
            $eventClass = EventClass::forSubject($activity)->first();
            $tableRows
            = [
                $activity->id,
                $activity->name,
                $eventClass?->id ?? 'UNKNOWN',
                $eventClass?->name ?? 'UNKNOWN',
                $eventClass?->review_status->value ?? 'UNKNOWN',
            ];
        }

        $this->table(
            ['Activity ID', 'Activity name', 'Event Class ID', 'Event Class Name', 'Review Status'],
            $tableRows,
        );

        if (! $this->option('enrollments')) {
            return self::SUCCESS;
        }

        $tableRows = [];
        /** @var Enrollment $enrollment */
        foreach ($activity->enrollments()->withoutGlobalScopes()->lazy(10) as $enrollment) {
            $eventObject = EventObject::forSubject($enrollment)->first();
            $tableRows[] = [
                $enrollment->id,
                $enrollment->user?->name,
                $enrollment->state->title,
                $eventObject?->id ?? 'UNKNOWN',
                $eventObject?->state->value ?? 'UNKNOWN',
            ];
        }

        $this->table(
            ['Enrollment ID', 'Enrollment owner', 'Enrollment status', 'Event Object ID', 'Event Object State'],
            $tableRows,
        );
    }

    /**
     * Interacts with the user.
     *
     * This method is executed before the InputDefinition is validated.
     * This means that this is the only place where the command can
     * interactively ask for values of missing required arguments.
     *
     * @return void
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('all')) {
            $input->setArgument('activity', 'all');
        }

        parent::interact($input, $output);
    }
}
