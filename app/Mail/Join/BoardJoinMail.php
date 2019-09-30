<?php

namespace App\Mail\Join;

use Laravel\Nova\Nova;
use App\Models\JoinSubmission;
use App\Nova\Resources\JoinSubmission as NovaJoinSubmission;

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
        // Build link to admin panel
        $adminRoute = implode('/', [
            Nova::path(),
            'resources',
            NovaJoinSubmission::uriKey(),
            $this->submission->id
        ]);

        // Render view
        return $this->markdown('emails.join.board')->with(['adminRoute' => $adminRoute]);
    }

    /**
     * @inheritDoc
     */
    protected function createSubject(JoinSubmission $submission): string
    {
        return sprintf('[site] Nieuwe aanmelding van %s.', $submission->name);
    }
}
