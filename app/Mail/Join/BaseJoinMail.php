<?php

declare(strict_types=1);

namespace App\Mail\Join;

use App\Models\JoinSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Shared elements for the join mail
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
abstract class BaseJoinMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * The "reply to" recipients of the message.
     * @var array
     */
    public $replyTo = [
        [
            'name' => 'Bestuur Gumbo Millennium',
            'address' => 'bestuur@gumbo-millennium.nl'
        ]
    ];

    /**
     * Registry submission
     * @var JoinSubmission
     */
    public $submission;

    /**
     * Create a new message instance.
     * @param JoinSubmission $submission Submission to send
     * @return void
     */
    public function __construct(JoinSubmission $submission)
    {
        $this->submission = $submission;
        $this->subject = $this->createSubject($submission);
    }

    /**
     * Build the message.
     * @return $this
     */
    abstract public function build();

    /**
     * Returns the subject
     * @param JoinSubmission $submission
     * @return string
     */
    abstract protected function createSubject(JoinSubmission $submission): string;
}
