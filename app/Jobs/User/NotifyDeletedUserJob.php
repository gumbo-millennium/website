<?php

declare(strict_types=1);

namespace App\Jobs\User;

use App\Mail\AccountDeletedMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class NotifyDeletedUserJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private User $user)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = $this->user;

        if (! $user->trashed()) {
            $this->fail("User {$user->id} was not deleted");
        }

        $message = new AccountDeletedMail($user);

        Mail::to($user)->send($message);
    }
}
