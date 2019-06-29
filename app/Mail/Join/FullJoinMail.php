<?php

namespace App\Mail\Join;

use App\Models\JoinSubmission;

class FullJoinMail extends BaseJoinMail
{
    /**
     * @inheritDoc
     */
    public function build()
    {
        return $this->markdown('emails.join.full');
    }

    /**
     * @inheritDoc
     */
    protected function createSubject(JoinSubmission $submission) : string
    {
        return '🎉 Welkom bij Gumbo Millennium 🎉';
    }
}
