<?php

declare(strict_types=1);

namespace App\Console\Commands\GoogleWallet;

use App\Enums\Models\GoogleWallet\ReviewStatus;
use App\Models\Activity;
use App\Models\GoogleWallet\EventClass;
use App\Services\Google\WalletService;
use GuzzleHttp\Exception\GuzzleException;
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
    google-wallet:create-missing-activities {--dry-run : Only show what would be created}
    CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates all missing Google Wallet Event Ticket Classes for recent activities';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(WalletService $walletService)
    {
        $dryRun = $this->option('dry-run');

        $expectedActivities = Activity::query()
            ->where('start_date', '>', Date::today())
            ->get();

        $walletClasses = EventClass::query()
            ->whereHasMorph('subject', Activity::class)
            ->whereIn('review_status', [ReviewStatus::Approved, ReviewStatus::Rejected])
            ->get()
            ->keyBy('subject_id');

        foreach ($expectedActivities as $activity) {
            if ($walletClasses->has($activity->id)) {
                if ($dryRun) {
                    $this->line("Activity {$activity->id}: <fg=gray>EventTicketClass exists</>");
                }

                continue;
            }

            if ($dryRun) {
                $this->line("Activity {$activity->id}: <fg=green>Will create EventTicketClass</>");

                continue;
            }

            try {
                $this->line("Activity {$activity->id}: Creating EventTicketClass...", null, OutputInterface::VERBOSITY_VERBOSE);

                $result = $walletService->writeEventClassForActivity($activity);

                $this->line("Activity {$activity->id}: <fg=green>EventTicketClass created succesfully</>");
                $this->line("Activity {$activity->id}: <fg=green>EventTicketClass ID: {$result->id}</>");
            } catch (GuzzleException $exception) {
                $this->error("HTTP error while creating EventTicketClass for activity {$activity->id}");

                $this->line($exception->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
