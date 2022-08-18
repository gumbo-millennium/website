<?php

declare(strict_types=1);

namespace App\Jobs\GoogleWallet;

use App\Models\Activity;
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

class CreateEventTicketClassJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public Activity $activity;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Activity $activity)
    {
        $this->activity = $activity;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(WalletService $walletService)
    {
        // Check if already exists
        if ($walletService->getEventTicketClass($this->activity)) {
            Log::warning('Tried to create a Google Wallet Ticket class for {activity}, but one already exists', [
                'activity' => $this->activity->id,
            ]);

            $this->fail(new RuntimeException("Ticket Class for Activity #{$this->activity->id} already exists"));

            return;
        }

        // Construct class
        $activityClass = $walletService->makeActivityTicketClass($this->activity);

        try {
            // Try inserting the class
            $walletService->insertEventTicketClass($activityClass);
        } catch (RequestException $exception) {
            Log::warning('Failed to create Google Wallet Ticket class for {activity}: {exception}', [
                'activity' => $this->activity->id,
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
        return sprintf('%05d', $this->activity->id);
    }
}
