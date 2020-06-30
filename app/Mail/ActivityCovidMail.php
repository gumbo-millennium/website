<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Swift_Message;

class ActivityCovidMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    protected Enrollment $enrollment;

    /**
     * Create a new message instance.
     * @return void
     */
    public function __construct(Enrollment $enrollment)
    {
        $this->enrollment = $enrollment;
    }

    /**
     * Build the message.
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
        $this->subject("✅ Laatste informatie voor {$activity->name}");

        // Add unsubscribe header
        $this->withSwiftMessage(
            // phpcs:ignore PSR2.Methods.FunctionCallSignature.MultipleArguments
            static fn(Swift_Message $message) => $message->getHeaders()->addTextHeader("List-Unsubscribe", $cancelUrl)
        );

        // Render
        return $this->markdown('mail.activity.covid', [
            'activity' => $activity,
            'enrollment' => $enrollment,
            'cancelUrl' => $cancelUrl,
            'cancelType' => $cancelType,
            'participant' => $user
        ]);
    }
}
