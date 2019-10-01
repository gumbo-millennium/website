<?php

namespace App\Mail\Join;

use App\Models\JoinSubmission;

class SimpleJoinMail extends BaseJoinMail
{
    /**
     * @inheritDoc
     */
    public function build()
    {
        return $this->markdown('emails.join.simple');
    }

    /**
     * @inheritDoc
     */
    protected function createSubject(JoinSubmission $submission): string
    {
        return '🎉 Bedankt voor je aanmelding bij Gumbo Millennium 🎉';
    }
}
