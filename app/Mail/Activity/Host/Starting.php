<?php

declare(strict_types=1);

namespace App\Mail\Activity\Host;

use App\Models\Activity;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Starting extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(private Activity $activity)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Activiteit {$this->activity->name} begint bijna",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $ticketCount = $this->activity->tickets()->count();
        $enrollmentCount = $this->activity->enrollments()->stable()->count();

        return new Content(
            markdown: 'mail.activity.host.starting',
            with: [
                'activity' => $this->activity,
                'ticketCount' => $ticketCount,
                'enrollmentCount' => $enrollmentCount,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
