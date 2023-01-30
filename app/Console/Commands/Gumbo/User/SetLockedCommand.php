<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo\User;

use App\Console\Commands\Traits\FindsUserTrait;
use App\Helpers\Str;
use Illuminate\Console\Command;

/**
 * Grants or revokes Super Admin priviliges on a user.
 */
class SetLockedCommand extends Command
{
    use FindsUserTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
    gumbo:user:lock
        {user : Email or ID of the user to lock}
        {--unlock : Remove lock from account}
    CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Locks or unlock the given account';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $user = $this->getUserArgument();

        if (! $user) {
            $this->error('Cannot find user.');

            return Command::FAILURE;
        }

        $unlock = $this->option('unlock');

        $this->line("Name:  <info>{$user->name}</>");
        $this->line("ID:    <comment>{$user->id}</>");
        $this->line("Email: <comment>{$user->email}</>");
        $this->line("Alias: <comment>{$user->alias}</>");
        $this->line('');
        $this->line(sprintf(
            'Currently locked: <info>%s</>',
            $user->isLocked() ? 'Yes' : 'No',
        ));
        $this->line('');
        if (! $this->confirm('Is this the correct user', false)) {
            $this->warn('Command aborted.');

            return Command::FAILURE;
        }

        $user->locked = ! $unlock;

        // Reset remember token to force a logout
        if ($user->isLocked()) {
            $user->setRememberToken(Str::random(60));
        }

        $user->save();

        $this->line(sprintf(
            '%s user account <info>%s</>.',
            $unlock ? 'Unlocked' : 'Locked',
            $user->name,
        ));

        return Command::SUCCESS;
    }
}
