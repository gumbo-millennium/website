<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo;

use App\Jobs\UpdateConscriboUserJob;
use App\Models\User;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Updates all users from Conscribo
 */
class UpdateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gumbo:update-user {user? : User to update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the users via the Conscribo API';

    /**
     * Execute the console command.
     *
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

            // Keep current roles
            $currentRoles = $user->roles;

            // Update user from API
            $this->line(sprintf('Updating <info>%s</>.', $user->name), null, OutputInterface::VERBOSITY_VERBOSE);
            UpdateConscriboUserJob::dispatchNow($user);

            // Get new roles
            $user->refresh();
            $newRoles = $user->roles;

            // Get role update
            $separator = '</>, <info>';
            $added = $newRoles->whereNotIn('name', $currentRoles->pluck('name'))->pluck('title')->implode($separator);
            $removed = $currentRoles->whereNotIn('name', $newRoles->pluck('name'))->pluck('title')->implode($separator);
            $existing = $currentRoles->whereIn('name', $newRoles->pluck('name'))->pluck('title')->implode($separator);

            // Log stuff
            $this->line('Update complete.', null, OutputInterface::VERBOSITY_VERBOSE);
            $this->line("Added roles: <info>{$added}</>", null, OutputInterface::VERBOSITY_VERY_VERBOSE);
            $this->line("Removed roles: <info>{$removed}</>", null, OutputInterface::VERBOSITY_VERY_VERBOSE);
            $this->line("Existing roles: <info>{$existing}</>", null, OutputInterface::VERBOSITY_VERY_VERBOSE);
        }

        // Done
        $this->info('Pulled new updates from Conscribo API');
    }
}
