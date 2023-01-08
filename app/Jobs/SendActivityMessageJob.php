<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\ActivityMessageMail;
use App\Models\ActivityMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;

/**
 * @method static self dispatch(ActivityMessage $activityMessage)
 */
class SendActivityMessageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected ActivityMessage $activityMessage;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ActivityMessage $activityMessage)
    {
        $this->activityMessage = $activityMessage;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $activityMessage = $this->activityMessage;

        // Stop if something seems off, prevent sending the same message twice.
        if ($activityMessage->sent_at !== null || $activityMessage->trashed()) {
            return;
        }

        $count = 0;

        foreach ($activityMessage->getEnrollmentsCursor() as $enrollment) {
            Mail::to($enrollment->user)
                ->queue(new ActivityMessageMail(
                    $enrollment,
                    $activityMessage,
                ));

            $count++;
        }

        // Save the changes to the message.
        $activityMessage->recipients = $count;
        $activityMessage->sent_at = Date::now();
        $activityMessage->save();

        // If the model was trashed while sending the message, restore it.
        if ($activityMessage->refresh()->trashed()) {
            $activityMessage->restore();
        }

        // Touch the activity, to ensure third parties update accordingly.
        $activityMessage->activity->touch();
    }
}
