<?php

declare(strict_types=1);

namespace App\Console\Commands\GoogleWallet;

use App\Helpers\Str;
use App\Models\Enrollment;
use App\Models\GoogleWallet\EventObject;
use App\Services\Google\WalletService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;

class UpdateEnrollmentCommand extends GoogleWalletCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-wallet:enrollment {enrollment}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Writes the Google Wallet Event Ticket Object for the given Enrollment ID (creates or updates)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(WalletService $walletService)
    {
        $enrollment = $this->argument('enrollment');
        $enrollment = Enrollment::find($enrollment);

        if (! $enrollment) {
            $this->error('Enrollment not found');

            return 1;
        }

        // Check state
        $exists = EventObject::forSubject($enrollment)->exists();
        $action = $exists ? 'Update' : 'Create';

        // Check state
        $this->line("Starting {$action} of EventTicketObject...");

        try {
            $walletService->writeEventObjectForEnrollment($enrollment);

            $this->line(Str::ucfirst("{$action} <info>OK</>"));

            return Command::SUCCESS;
        } catch (GuzzleException $e) {
            $this->line(Str::ucfirst("{$action} <fg=red>FAIL</>"));
            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
