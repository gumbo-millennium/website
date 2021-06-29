<?php

declare(strict_types=1);

namespace App\Jobs\Activities;

use App\Models\Activity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use LogicException;

/**
 * @method static static dispatch(Activity $activity)
 * @method static static dispatchNow(Activity $activity)
 * @method static static dispatchAfterResponse(Activity $activity)
 */
abstract class ActivityJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Activity $activity;

    public function __construct(Activity $activity)
    {
        $this->activity = $activity;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        throw new LogicException('Someone forgot to implement a handle method!');
    }
}
