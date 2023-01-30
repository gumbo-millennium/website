<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo\User;

use App\Console\Commands\Traits\FindsUserTrait;
use Illuminate\Console\Command;

/**
 * Grants or revokes Super Admin priviliges on a user.
 */
class SetAdminCommand extends Command
{
    use FindsUserTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
    gumbo:user:admin
        {user : Email or ID of the user to promote}
        {--revoke : Remove admin priviliges}
    CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grants or revokes superadmin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = $this->getUserArgument();

        if (! $user) {
            $this->error('Cannot find user.');

            return Command::FAILURE;
        }

        $demote = $this->option('revoke');

        $this->line("Name:  <info>{$user->name}</>");
        $this->line("ID:    <comment>{$user->id}</>");
        $this->line("Email: <comment>{$user->email}</>");
        $this->line("Alias: <comment>{$user->alias}</>");
        $this->line('');
        $this->line(sprintf(
            'Current roles: <info>%s</>',
            $user->roles()->pluck('title')->implode('</>, <info>'),
        ));
        $this->line(sprintf(
            'Current permissions: <info>%s</>',
            $user->getDirectPermissions()->pluck('title')->implode('</>, <info>'),
        ));
        $this->line('');
        if (! $this->confirm('Is this the correct user', false)) {
            $this->warn('Command aborted.');

            return Command::FAILURE;
        }

        if ($demote) {
            $user->revokePermissionTo('super-admin');
            $user->save();

            $this->line(sprintf(
                'Removed super admin from <info>%s</>.',
                $user->name,
            ));

            return Command::SUCCESS;
        }

        $user->givePermissionTo('super-admin');
        $user->save();

        $this->line(sprintf(
            'Granted super admin to <info>%s</>.',
            $user->name,
        ));

        return Command::SUCCESS;
    }
}
