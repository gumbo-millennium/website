<?php

declare(strict_types=1);

namespace App\Console\Commands\Permissions;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\Console\Helper\TableCell;

class ListRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:role
                                {permission? : Permission to check}
                                {--p|pretty : Print titles instead of names}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Build default query
        $permissionQuery = Permission::query()->with('roles');

        // Get permisison name, if provided
        $permission = $this->argument('permission');

        // Check for wildcard and permission name
        if ($permission !== null && stripos($permission, '*') !== false) {
            // Run wildcard search
            $permissionQuery->where('name', 'LIKE', str_replace('*', '%', $permission));
        } elseif ($permission !== null) {
            // Run regular search
            $permissionQuery->where('name', $permission);
        }

        // Run the query
        $allPerms = $permissionQuery->get();

        // Get all roles
        $allRoles = Role::all()->sortBy('name');

        // Get display property
        $view = $this->option('pretty') ? 'title' : 'name';

        $scopedList = collect();
        foreach ($allPerms as $perm) {
            $scopedList->push(
                collect($perm->{$view})
                    ->concat($allRoles->map(static fn ($role) => $perm->roles->contains($role) ? '<info>✔</>' : ''))
            );
        }

        // Generate headers
        $headers = collect(['Permission'])
            ->concat($allRoles->map(static fn ($role) => $role->{$view}));

        // Check if scopedList is empty
        if ($scopedList->count() === 0) {
            $scopedList->push([
                new TableCell(
                    '<comment>No results found.</>',
                    ['colspan' => $headers->count()]
                ),
            ]);
        }

        $this->table($headers, $scopedList);
    }
}
