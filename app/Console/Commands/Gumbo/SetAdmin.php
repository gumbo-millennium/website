<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo;

use App\Console\Commands\Traits\FindsUserTrait;
use Illuminate\Console\Command;

/**
 * Grants or revokes Super Admin priviliges on a user.
 */
class SetAdmin extends Command
{
    use FindsUserTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gumbo:set-admin {user} {--revoke}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grants or revokes superadmin';

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
     */
    public function handle()
    {
        $user = $this->getUserArgument();

        if (! $user) {
            $this->error('Cannot find user');

            return false;
        }

        $demote = $this->option('revoke');

        $this->line("Name:  <info>{$user->name}</>");
        $this->line("ID:    <comment>{$user->id}</>");
        $this->line("Email: <comment>{$user->email}</>");
        $this->line("Alias: <comment>{$user->alias}</>");
        $this->line('');
        $this->line(sprintf(
            'Current roles: <info>%s</>',
            $user->roles()->pluck('title')->implode('</>, <info>')
        ));
        $this->line(sprintf(
            'Current permissions: <info>%s</>',
            $user->getDirectPermissions()->pluck('title')->implode('</>, <info>')
        ));
        $this->line('');
        if (! $this->confirm('Is this the correct user', false)) {
            $this->warn('User aborted');

            return false;
        }

        if ($demote) {
            $user->revokePermissionTo('super-admin');
            $user->save();

            $this->line(sprintf(
                'Removed super admin from <info>%s</>.',
                $user->name
            ));

            return true;
        }

        $user->givePermissionTo('super-admin');
        $user->save();

        $this->line(sprintf(
            'Granted super admin to <info>%s</>.',
            $user->name
        ));

        return true;
    }
}
