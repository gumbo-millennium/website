<?php

declare(strict_types=1);

namespace App\Mail;

use App\Facades\Markdown;
use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Swift_Message;

class ActivityMessageMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    protected Enrollment $enrollment;

    protected string $title;

    protected string $body;

    /**
     * Preps a new custom user message.
     *
     * @param Enrollment $enrollment
     * @param string $title
     * @param string $body
     */
    public function __construct(Enrollment $enrollment, string $title, string $body)
    {
        $this->enrollment = $enrollment;
        $this->title = $title;
        $this->body = $body;
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
        $activity = $enrollment->activity;
        $user = $enrollment->user;

        // Get link
        $cancelUrl = \route('enroll.remove', compact('activity'));
        $cancelType = 'cancel';
        if ($enrollment->price > 0 && $activity->enrollment_open) {
            $cancelUrl = \route('enroll.transfer', compact('activity'));
            $cancelType = 'transfer';
        }

        // Set subject
        $this->subject("Update voor {$activity->name}: {$this->title}");

        // Add unsubscribe header
        $this->withSwiftMessage(
            static fn (Swift_Message $message) => $message
                ->getHeaders()
                ->addTextHeader("List-Unsubscribe", $cancelUrl)
        );

        // Render
        return $this->markdown('mail.activity.update', [
            'activity' => $activity,
            'enrollment' => $enrollment,
            'cancelUrl' => $cancelUrl,
            'cancelType' => $cancelType,
            'participant' => $user,
            'userTitle' => $this->title,
            'userBody' => Markdown::parseSafe($this->body),
        ]);
    }
}
