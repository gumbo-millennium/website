<?php

declare(strict_types=1);

namespace App\Listeners\Public;

use App\Events\Public\UserJoinedEvent;
use App\Mail\Join\BoardJoinMail;
use App\Mail\Join\UserJoinMail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class UserJoinedListener
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(UserJoinedEvent $event)
    {
        $submission = $event->joinSubmission;

        // Send mail to submitter
        Mail::to($submission->only('name', 'email'))
            ->queue(new UserJoinMail($submission));

        // Send mail to board
        Mail::to(Config::get('gumbo.mail-recipients.board'))
            ->queue(new BoardJoinMail($submission));
    }
}
