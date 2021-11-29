<?php

declare(strict_types=1);

namespace App\Jobs\Activities;

use App\Helpers\Arr;
use App\Models\ScheduledMail;
use App\Models\States\Enrollment\Cancelled as CancelledState;
use App\Models\States\Enrollment\Refunded as RefundedState;
use App\Notifications\Activities\ActivityFeatureNotification;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class CheckActivityForFeatureMails extends ActivityJob
{
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Don't run on started, cancelled or postponed events
        if ($this->activity->start_date < Date::now()) {
            return;
        }

        if ($this->activity->is_cancelled || $this->activity->is_postponed) {
            return;
        }

        $features = collect($this->activity->features)
            ->filter()
            ->keys()
            ->all();

        Log::debug('Read features {features} on {activity}', [
            'features' => $features,
            'activity' => $this->activity->only(['id', 'name', 'slug']),
        ]);

        foreach ($features as $feature) {
            // Get feature config
            $mailConfig = Config::get("gumbo.activity-features.{$feature}.mail", []);

            // Check against expected values
            if (! Arr::has($mailConfig, ['send', 'subject', 'body'])) {
                continue;
            }

            // Check time offset
            $sendOffset = Arr::get($mailConfig, 'send');

            try {
                $sendTime = (clone $this->activity->start_date)->sub($sendOffset);
            } catch (Exception $timeException) {
                Log::error('Failed to parse feature {feature-name} time offset {send-offset}', [
                    'feature-name' => $feature,
                    'mail-config' => $mailConfig,
                    'send-offset' => $sendOffset,
                ]);

                continue;
            }

            // Date not yet reached
            if ($sendTime > Date::now()) {
                continue;
            }

            // Check if we've sent it
            $scheduledMail = ScheduledMail::findForModelMail($this->activity, "feature.{$feature}");
            if ($scheduledMail->is_sent) {
                continue;
            }

            // Flag as sending
            $scheduledMail->scheduled_for = Date::now();
            $scheduledMail->save();

            /** @var \Illuminate\Support\Collection<\App\Models\Enrollment> $enrollments */
            $enrollments = $this->activity->enrollments()
                ->withoutTrashed()
                ->whereNotState('state', [CancelledState::class, RefundedState::class])
                ->with('user')
                ->get();

            // Notify each guest in bulk
            Notification::send(
                $enrollments->pluck('user'),
                new ActivityFeatureNotification($this->activity, $feature),
            );

            // Mark as sent
            $scheduledMail->sent_at = Date::now();
            $scheduledMail->save();
        }
    }
}
