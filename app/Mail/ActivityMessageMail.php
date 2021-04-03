<?php

declare(strict_types=1);

namespace App\Mail;

use App\Facades\Markdown;
use App\Models\ActivityMessage;
use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ActivityMessageMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    protected Enrollment $enrollment;

    protected ActivityMessage $activityMessage;

    /**
     * Preps a new custom user message.
     *
     * @param Enrollment $enrollment
     * @param string $title
     * @param string $body
     */
    public function __construct(Enrollment $enrollment, ActivityMessage $activityMessage)
    {
        $this->enrollment = $enrollment;
        $this->activityMessage = $activityMessage;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Get props
        $enrollment = $this->enrollment;
        $message = $this->activityMessage;

        // Get subs
        $activity = $message->activity;
        $user = $enrollment->user;

        // Get link
        $cancelUrl = \route('enroll.remove', compact('activity'));
        $cancelType = 'cancel';
        if ($enrollment->price > 0 && $activity->enrollment_open) {
            $cancelUrl = \route('enroll.transfer', compact('activity'));
            $cancelType = 'transfer';
        }

        // Set subject
        $this->subject("Update voor {$activity->name}: {$message->title}");

        // Render
        return $this->markdown('mail.activity.update', [
            'activity' => $activity,
            'enrollment' => $enrollment,
            'cancelUrl' => $cancelUrl,
            'cancelType' => $cancelType,
            'participant' => $user,
            'userTitle' => $message->title,
            'userBody' => Markdown::parseSafe($message->body),
        ]);
    }

    public function getActivityMessage(): ActivityMessage
    {
        return $this->activityMessage;
    }
}
