<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled as CancelledState;
use App\Models\States\Enrollment\Refunded as RefundedState;
use App\Models\User;
use App\Notifications\VerifyEmail;
use Illuminate\Console\Command;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class TestMailCommand extends Command
{
    private const EMAIL_CLASSES = [
        VerifyEmail::class,
    ];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:mail {email : e-mail address of the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends a test e-mail to the user';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $email = $this->argument('email');
        /** @var User $user */
        $user = User::whereEmail($email)->first();

        if (!$user) {
            $this->line("Cannot find a user with the email address <info>$email</>.");
            return false;
        }

        $this->line("Recieved user <info>{$user->name}</>.");

        // Find enrollment
        /** @var Enrollment $enrollment */
        $enrollment = Enrollment::query()
            ->whereUserId($user->id)
            ->whereNotState('state', [CancelledState::class, RefundedState::class])
            ->orderByDesc('updated_at')
            ->with('activity')
            ->first();

        $this->line(sprintf(
            'Received enrollment <info>%s</> for <comment>%s</>.',
            $enrollment ? $enrollment->id : "NULL",
            $enrollment ? $enrollment->activity->name : "NULL"
        ));

        // Store on app
        app()->instance(User::class, $user);
        if ($enrollment) {
            app()->instance(Enrollment::class, $enrollment);
            app()->instance(Activity::class, $enrollment->activity);
        }

        $this->line('Sending emails');

        foreach (self::EMAIL_CLASSES as $email) {
            $emailClass = class_basename($email);
            $this->line("Creating <info>{$emailClass}</>...");
            $instance = app()->make($email);

            if ($instance instanceof Notification) {
                $this->line("Sending <info>{$emailClass}</> as <comment>Notification</>...");
                $user->notifyNow($instance);
            } elseif ($instance instanceof Mailable) {
                $this->line("Sending <info>{$emailClass}</> as <comment>Mail</>...");
                Mail::to($user)->send($instance);
            }
        }
    }
}
