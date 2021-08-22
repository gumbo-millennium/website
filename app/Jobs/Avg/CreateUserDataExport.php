<?php

declare(strict_types=1);

namespace App\Jobs\Avg;

use App\Models\DataExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * @method static \Illuminate\Foundation\Bus\PendingDispatch dispatch(DataExport $export)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch dispatchNow(DataExport $export)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch dispatchAfterResponse(DataExport $export)
 */
class CreateUserDataExport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private DataExport $export;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(DataExport $export)
    {
        $this->export = $export;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // TODO: Implement handle() method.
    }
}
