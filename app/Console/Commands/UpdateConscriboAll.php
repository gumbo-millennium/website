<?php

namespace App\Console\Commands;

use App\Jobs\UpdateConscriboUserJob;
use App\Models\User;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Updates all users from Conscribo
 */
class UpdateConscriboAll extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'gumbo:user-update {user? : User to update}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Updates the users via the Conscribo API';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        // Prep query
        $users = User::query();

        // Filter by e-mail, if set
        $email = $this->argument('user');
        if ($email) {
            $users = $users->whereEmail($email);
        }

        // Update all matching users
        foreach ($users->cursor() as $user) {
            // Help IDE and get sane
            assert($user instanceof User, "User invalid");

            // Skip non-verified users
            if (!$user->hasVerifiedEmail()) {
                $this->line(sprintf('Skipped <info>%s</>.', $user->name), null, OutputInterface::VERBOSITY_VERBOSE);
                continue;
            }

            // Update user from API
            $this->line(sprintf('Updating <info>%s</>.', $user->name), null, OutputInterface::VERBOSITY_VERBOSE);
            UpdateConscriboUserJob::dispatchNow($user);
        }

        // Done
        $this->info('Pulled new updates from Conscribo API');
    }
}
