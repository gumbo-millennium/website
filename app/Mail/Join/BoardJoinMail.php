<?php

declare(strict_types=1);

namespace App\Mail\Join;

use App\Models\JoinSubmission;
use App\Nova\Resources\JoinSubmission as NovaJoinSubmission;
use Laravel\Nova\Nova;

/**
 * Email sent to the board concerning the new member.
 */
class BoardJoinMail extends BaseJoinMail
{
    /**
     * Board should not reply to these mails.
     *
     * @var array
     */
    public $replyTo = [];

    /**
     * @inheritDoc
     */
    public function build()
    {
        // Add precise link to admin panel
        $actionUrl = implode('/', [
            secure_url(Nova::path()),
            'resources',
            NovaJoinSubmission::uriKey(),
            $this->submission->id,
        ]);

        // Render view
        return $this->markdown('mail.join.board')->with(['actionUrl' => $actionUrl]);
    }

    /**
     * @inheritDoc
     */
    protected function createSubject(JoinSubmission $submission): string
    {
        return sprintf('[site] Nieuwe aanmelding van %s.', $submission->name);
    }
}
