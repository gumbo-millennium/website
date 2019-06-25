<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use App\Console\Commands\Traits\FindsUserTrait;

/**
 * Adds a role via CLI
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class GumboRoleCommand extends Command
{
    use FindsUserTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gumbo:role {user} {role} {--force} {--revoke}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Give or revoke a user with the given ID, email or alias the given role';

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
        $user = $this->getUserArgument();

        if (!$user) {
            $this->error('Cannot find user');
            return false;
        }

        $roleName = $this->argument('role');
        try {
            $role = Role::findByName($roleName);
        } catch (RoleDoesNotExist $e) {
            $this->error("Cannot find role named \"{$roleName}\"");
            return false;
        }

        $force = $this->option('force');
        $revoke = $this->option('revoke');

        $this->line("Name:  <info>{$user->name}</>");
        $this->line("ID:    <comment>{$user->id}</>");
        $this->line("Email: <comment>{$user->email}</>");
        $this->line("Alias: <comment>{$user->alias}</>");
        $this->line("");
        $this->line(sprintf(
            'Current roles: <info>%s</>',
            $user->roles()->pluck('title')->implode('</>, <info>')
        ));
        $this->line("");
        if (!$force && !$this->confirm('Is this the correct user')) {
            $this->warn('User aborted');
            return false;
        }

        if ($revoke) {
            $user->removeRole($role);
            $user->save();

            $this->line(sprintf(
                'Removed role <comment>%s</> (<comment>%s</>) from <info>%s</>.',
                $role->title,
                $role->name,
                $user->name
            ));

            return true;
        }

        $user->assignRole($role);
        $user->save();

        $this->line(sprintf(
            'Added role <info>%s</> (%s) to <info>%s</>.',
            $role->title,
            $role->name,
            $user->name
        ));

        return true;
    }
}
