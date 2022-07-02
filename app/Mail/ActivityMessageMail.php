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
        $enrollmentUrl = route('enroll.show', $activity);

        // Set subject
        $this->subject($message->subject ?: "Update voor {$activity->name}");

        // Render
        return $this->markdown('mail.activity.update', [
            'activity' => $activity,
            'enrollment' => $enrollment,
            'enrollmentUrl' => $enrollmentUrl,
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
