<?php

declare(strict_types=1);

namespace App\Jobs\GoogleWallet;

use App\Models\Enrollment;
use App\Services\Google\WalletService;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CreateEventTicketObjectJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public Enrollment $enrollment;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Enrollment $enrollment)
    {
        $this->enrollment = $enrollment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(WalletService $walletService)
    {
        // Check if the class exists
        if (! $walletService->getEventTicketClass($this->enrollment->activity)) {
            Log::warning('Tried to create a Google Wallet Ticket object for {enrollment}, but the class doesn\'t exist yet', [
                'enrollment' => $this->enrollment->id,
            ]);
        }

        // Check if the object does not yet exist
        if ($walletService->getEventTicketObject($this->enrollment)) {
            Log::warning('Tried to create a Google Wallet Ticket object for {enrollment}, but one already exists', [
                'enorllment' => $this->enrollment->id,
            ]);

            $this->fail(new RuntimeException("Ticket Object for Enrollment #{$this->enrollment->id} already exists"));

            return;
        }

        // Construct object
        $enrollmentObject = $walletService->makeEnrollmentTicketObject($this->enrollment);

        try {
            // Try inserting the class
            $walletService->insertEventTicketObject($enrollmentObject);
        } catch (RequestException $exception) {
            Log::warning('Failed to create Google Wallet Ticket object for {enrollment}: {exception}', [
                'enrollment' => $this->enrollment->id,
                'exception' => $exception,
            ]);

            $this->fail($exception);

            return;
        }
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return sprintf('%05d', $this->enrollment->id);
    }
}
