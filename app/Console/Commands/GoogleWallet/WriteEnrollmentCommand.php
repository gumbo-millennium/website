<?php

declare(strict_types=1);

namespace App\Console\Commands\GoogleWallet;

use App\Jobs\GoogleWallet\UpdateEventTicketClassJob;
use App\Models\Activity;
use App\Services\Google\WalletService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class WriteEnrollmentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-wallet:write-enrollment {activity} {enrollment}';

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
        $activityArgument = $this->argument('activity');
        $activity = Activity::query(fn (Builder $query) => $query->orWhere([
            ['id', $activityArgument],
            ['slug', $activityArgument],
        ]))->first();

        if (! $activity) {
            $this->error("Cannot find activity <fg=white>{$activityArgument}</>.");

            return Command::FAILURE;
        }

        $enrollmentArgument = $this->argument('enrollment');
        $enrollment = $activity->enrollments()->query(
            fn (Builder $query) => $query
                ->where('id', $enrollmentArgument)
                ->orWhereHas('user', fn (Builder $query) => $query->where(['email', $enrollmentArgument])),
        )->first();

        if (! $enrollment) {
            $this->error("Cannot find enrollment <fg=white>{$enrollmentArgument}</> in activity <fg=gray>{$activity->name}</>");

            return Command::FAILURE;
        }

        // Check state
        if ($walletService->getEventTicketClass($activity)) {
            $this->line('Updating existing EventTicketClass...');

            try {
                UpdateEventTicketClassJob::dispatchSync($activity);

                $this->line('Update <info>OK</info>');

                return Command::SUCCESS;
            } catch (GuzzleException $e) {
                $this->line('HTTP error while updating!');
                $this->error($e->getMessage());

                return Command::FAILURE;
            }
        }

        $this->line('Creating new EventTicketClass...');

        try {
            UpdateEventTicketClassJob::dispatchSync($activity);

            $this->line('Create <info>OK</info>');

            return Command::SUCCESS;
        } catch (GuzzleException $e) {
            $this->line('HTTP error while creating!');
            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
