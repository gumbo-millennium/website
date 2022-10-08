<?php

declare(strict_types=1);

namespace App\Console\Commands\GoogleWallet;

use App\Helpers\Str;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\GoogleWallet\EventClass;
use App\Services\Google\WalletService;
use Google\Service\Exception as ServiceException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;
use Symfony\Component\Console\Output\OutputInterface;

class CreateMissingActivitiesCommand extends GoogleWalletCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
    google-wallet:create-missing-activities
        {--dry-run : Only show what would be created}
        {--all : Update all eligible activities}
    CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates all missing Google Wallet Event Ticket Classes for recent activities';

    private WalletService $walletService;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(WalletService $walletService)
    {
        $this->walletService = $walletService;
        if (! $this->walletService->isEnabled()) {
            $this->warn('Google Wallet is not enabled, aborting.');

            return 0;
        }

        $dryRun = $this->option('dry-run');

        $expectedActivities = Activity::query()
            ->where('start_date', '>', Date::today())
            ->get();

        foreach ($expectedActivities as $activity) {
            $exists = EventClass::query()->forSubject($activity)->exists();
            $action = $exists ? 'update' : 'create';

            if ($exists && ! $this->option('all')) {
                $this->line("Activity {$activity->id}: <fg=gray>EventTicketClass exists</>");

                continue;
            }

            if ($dryRun) {
                $this->line("Activity {$activity->id}: <fg=green>Would {$action} EventTicketClass</>");

                continue;
            }

            $this->line("Activity {$activity->id}: <fg=green>{$action} EventTicketClass</>");

            try {
                $this->handleActivity($activity, $action);
            } catch (ServiceException $exception) {
                $this->error(Str::ucfirst("{$action} activity class failed for {$activity->id}"));

                $this->line($exception->getMessage());

                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }

    private function handleActivity(Activity $activity): void
    {
        $this->line("Processing Activity {$activity->id}...");

        $result = $this->walletService->writeEventClassForActivity($activity);

        $this->line("Processed Activity {$activity->id}: <fg=green>{$result->reviewStatus}</>");
        $this->line(" - Google Wallet ID: {$result->wallet_id}", null, OutputInterface::VERBOSITY_VERBOSE);

        foreach ($activity->enrollments()->stable()->get() as $enrollment) {
            $this->handleEnrollment($enrollment);
        }
    }

    private function handleEnrollment(Enrollment $enrollment): void
    {
        $this->line("Processing Enrollment {$enrollment->id} of {$enrollment->user->name}...", null, OutputInterface::VERBOSITY_VERBOSE);

        $result = $this->walletService->writeEventObjectForEnrollment($enrollment);

        $this->line("Processed Enrollment {$enrollment->id} of {$enrollment->user->name}");
        $this->line(" - Google Wallet ID: {$result->wallet_id}", null, OutputInterface::VERBOSITY_VERBOSE);
    }
}
