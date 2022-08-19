<?php

declare(strict_types=1);

namespace App\Console\Commands\GoogleWallet;

use App\Jobs\GoogleWallet\UpdateEventTicketClassJob;
use App\Models\Activity;
use App\Services\Google\WalletService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;

class WriteActivityCommand extends GoogleWalletCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-wallet:write-activity {activity}';

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
        $activity = Activity::query(fn ($query) => $query->orWhere([
            [
                'id',
                $activity,
            ],
            [
                'slug',
                $activity,
            ],
        ]))->first();

        if (! $activity) {
            $this->error('Activity not found');

            return 1;
        }

        // Check state
        if ($walletService->makeActivityTicketClass($activity)) {
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
