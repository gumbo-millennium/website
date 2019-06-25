<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class ListUserPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'perms:user
                            {user : User to check; e-mail, alias or ID}
                            {--p|pretty : Print titles instead of names}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finds user permissions';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get display property
        $view = $this->option('pretty') ? 'title' : 'name';

        // Find user
        $username = $this->argument('user');
        $user = User::where(function ($query) use ($username) {
            $query->where('id', $username)
                ->orWhere('email', $username);
        })->firstOrFail();

        // Find all permissions
        $allPerms = Permission::all();

        // Get user's own permission
        $userOwnPerm = $user->getDirectPermissions();

        // Get user's roles
        $userRolePerm = $user->roles;

        // Granted permission list
        $hasPerms = collect();

        // Loop through permissions
        foreach ($allPerms as $perm) {
            $why = collect();

            // If the user has this permission on it's account, add '(self)'
            if ($userOwnPerm->contains($perm)) {
                $why->push('(self)');
            }

            // Now check the roles
            $rolesWithPerm = $userRolePerm->filter(function ($role) use ($perm) {
                return $role->permissions->contains('name', $perm->name);
            })->pluck($view)->sort();

            // Add roles with the permission to the list fields
            $why = $why->concat($rolesWithPerm);

            $hasPerms->push([
                $perm->$view,
                $user->hasPermissionTo($perm) ? '<info>✔</>' : '',
                $why->implode(', ')
            ]);
        }

        $this->table(['Permission', 'Granted', 'Via'], $hasPerms);
    }
}
