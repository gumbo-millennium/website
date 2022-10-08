<?php

declare(strict_types=1);

namespace App\Jobs\GoogleWallet;

use App\Models\Activity;
use App\Models\Enrollment;
use App\Services\Google\WalletService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use LogicException;

class UpdateGoogleWalletResource implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use ShouldBeUnique;

    protected Model $model;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(WalletService $service)
    {
        $model = $this->model;

        match (get_class($model)) {
            Activity::class => $service->writeEventClassForActivity($model),
            Enrollment::class => $service->writeEventObjectForEnrollment($model),
            default => throw new LogicException('The model is not supported by the Google Wallet service.'),
        };
    }
}
