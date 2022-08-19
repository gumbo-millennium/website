<?php

declare(strict_types=1);

namespace App\Console\Commands\GoogleWallet;

use App\Jobs\GoogleWallet\CreateEventTicketClassJob;
use App\Models\Activity;
use App\Services\Google\WalletService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
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

        $expectedActivities = Activity::query(fn (Builder $query) => $query->orWhere([
            ['start_date', '>', Date::today()->subMonths(2)],
            ['end_date', '>', Date::today()->subMonths(2)],
        ]))->get();

        $existingEventTicketClasses = Collection::make($walletService->listEventTicketClasses())
            ->keyBy('id');

        foreach ($expectedActivities as $activity) {
            $activityClassId = $walletService->getActivityClassId($activity);
            if ($existingEventTicketClasses->has($activityClassId)) {
                if ($dryRun) {
                    $this->line("Activity {$activity->id}: <fg=gray>EventTicketClass exists</fg>");
                }

                continue;
            }

            if ($dryRun) {
                $this->line("Activity {$activity->id}: <fg=green>Will create EventTicketClass</fg>");

                continue;
            }

            try {
                $this->line("Activity {$activity->id}: Creating EventTicketClass...", null, OutputInterface::VERBOSITY_VERBOSE);
                CreateEventTicketClassJob::dispatchSync($activity);
                $this->line("Activity {$activity->id}: <fg=green>EventTicketClass created succesfully</>");
            } catch (GuzzleException $exception) {
                $this->error("HTTP error while creating EventTicketClass for activity {$activity->id}");
                $this->line($exception->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
