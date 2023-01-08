<?php

declare(strict_types=1);

namespace App\Console\Commands\GoogleWallet;

use App\Helpers\Str;
use App\Models\Activity;
use App\Models\GoogleWallet\EventClass;
use App\Services\Google\WalletService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;

class UpdateActivityCommand extends GoogleWalletCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
        google-wallet:activity
            {activity : ID or slug of the activity}
            {--with-enrollments : Also update the enrollments for this activity }
    CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Writes the Google Wallet Event Ticket Class for the given Activity (creates or updates)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(WalletService $walletService)
    {
        $activity = $this->argument('activity');
        $activity = Activity::find($activity) ?? Activity::findBySlug($activity);

        if (! $activity) {
            $this->error('Activity not found');

            return 1;
        }

        // Check state
        $exists = EventClass::forSubject($activity)->exists();
        $action = $exists ? 'Update' : 'Create';

        // Check state
        $this->line("Starting {$action} of EventTicketClass...");

        try {
            $walletService->writeEventClassForActivity($activity);

            $this->line(Str::ucfirst("{$action} <info>OK</>"));

            if ($this->option('with-enrollments')) {
                $this->line('Starting update of enrollments...');

                foreach ($activity->enrollments as $enrollment) {
                    $this->call('google-wallet:enrollment', [
                        'enrollment' => $enrollment->id,
                    ]);
                }

                $this->info('Completed update of enrollments.');
            }

            return Command::SUCCESS;
        } catch (GuzzleException $e) {
            $this->line(Str::ucfirst("{$action} <fg=red>FAIL</>"));
            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
