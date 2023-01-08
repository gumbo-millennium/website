<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\ActivityMessage;
use Illuminate\Support\Facades\Artisan;

class ActivityMessageObserver
{
    /**
     * Handle the ActivityMessage "created" event.
     */
    public function created(ActivityMessage $activityMessage): void
    {
        // Trigger a Google Wallet update
        Artisan::queue('google-wallet:activity', [
            'activity' => $activityMessage->activity->id,
            '--with-enrollments' => true,
        ]);
    }

    /**
     * Handle the ActivityMessage "updated" event.
     */
    public function updated(ActivityMessage $activityMessage): void
    {
        // Check if sent_at changed and, if it has, trigger a Google Wallet update
        if ($activityMessage->wasChanged('sent_at')) {
            Artisan::queue('google-wallet:activity', [
                'activity' => $activityMessage->activity->id,
                '--with-enrollments' => true,
            ]);
        }
    }
}
