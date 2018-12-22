<?php

namespace App\Console\Commands;

use App\Jobs\DeleteUserJob;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Deletes all users older than 90 days
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class GdprDeleteUser extends Command
{
    const FOUND_PRINT = <<<END
===============================
Found user <info>%s</>.

User ID:    <comment>%d</>
E-mail:     <comment>%s</>
===============================
END;

    /**
     * Warning shown when using --now
     *
     * @var string
     */
    const FORCE_WARNING = <<<END
======================= WARNING =======================

    You are forcing a delete on a user that
    was scheduled for deletion on <info>%s</>.

    The deletion is scheduled to take place after
    <info>%s</>.

    This is an <error>hightly destructive</> action
    which may have unknown side-effects.

    Are you ABSOLUTELY SURE you want to continue?

=======================================================
END;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gdpr:delete-user
                            {user : ID or e-mail of the user to delete}
                            {--now : Delete now, instead of after 90 days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes a given user';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get args
        $userId = $this->argument('user');
        $now = $this->option('now');

        // Check for user
        $user = User::withTrashed()->where(function ($query) use ($userId) {
            $query->where('id', $userId)
                ->orWhere('email', $userId);
        })->first();

        // No user = error
        if (!$user) {
            $this->error("Cannot find user by ID or e-mail [{$userId}]");
            $this->line('Please check and try again');
        }

        // inform user
        $this->line(sprintf(
            self::FOUND_PRINT,
            $user->name,
            $user->id,
            $user->email
        ));

        /**
         * If not forcing and already trashed, show that.
         */
        if (!$now && $user->trashed()) {
            $this->line("\nThe user <info>{$user->name}</info> is already scheduled for deletion.\n");

            // Report stats
            $this->line(sprintf(
                'Deletion scheduled on <info>%s</>',
                $user->deleted_at->toRfc7231String()
            ));
            $this->line(sprintf(
                'Deletion will occur after <info>%s</>',
                $user->deleted_at->addDays(90)->toRfc7231String()
            ));
            return false;
        }

        // Confirm deletion
        if (!$this->confirm(sprintf(
            'Are you sure you want to delete <comment>%s</> from the system?',
            $user->name
        ))) {
            $this->line('Command canceled');
            return false;
        };

        if (!$user->trashed()) {
            $user->delete();
            $date = today()->addDay(90)->toFormattedDateString();
            $this->line("User <info>{$user->name}</> flagged for deletion on {$date}.");
        }

        if (!$now) {
            // Report stats
            $this->line(sprintf(
                'Deletion scheduled on <info>%s</>',
                $user->deleted_at->toRfc7231String()
            ));
            $this->line(sprintf(
                'Deletion will occur after <info>%s</>',
                $user->deleted_at->addDays(90)->toRfc7231String()
            ));

            return true;
        }


        // We're forcing the delete. Make sure the user wants this.
        $this->warn(sprintf(
            self::FORCE_WARNING,
            $user->deleted_at->toDateString(),
            $user->deleted_at->addDays(90)->toRfc7231String()
        ));

        if (!$this->confirm(sprintf(
            'Confirm immediate removal of <comment>%s</>?',
            $user->name
        ))) {
            $this->line('Command canceled');
            return false;
        }

        $this->line("Scheduling immedate removal of user {$user->name}.");

        dispatch(new DeleteUserJob($user));

        $this->line('<info>Scheduled.</info>');

        $prompt = sprintf(
            'Would you like to send an e-mail to <info>%s</> to acknowledge deletion?',
            $user->name
        );

        if ($this->confirm($prompt)) {

        }
    }
}
