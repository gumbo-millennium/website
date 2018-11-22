<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Creates all roles required for ranks that users can have.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class PermissionSeeder extends Seeder
{
    /**
     * Permissions, in [name, title] format
     *
     * @var array
     */
    protected $permissions = [
        // Create file permissions
        ['file-add', 'Bestanden toevoegen'],
        ['file-edit', 'Bestanden bewerken'],
        ['file-delete', 'Bestanden verwijderen'],
        ['file-publish', 'Bestanden publiceren'],

        // Create file user permissions
        ['file-view', 'Bestanden bekijken (alleen metadata)'],
        ['file-download', 'Bestanden downloaden'],

        // Create file category permissions
        ['file-category-add', 'Bestandscategorieën toevoegen'],
        ['file-category-edit', 'Bestandscategorieën bewerken'],
        ['file-category-delete', 'Bestandscategorieën verwijderen'],

        // Manage content
        ['content', 'Eigen WordPress content beheren'],
        ['content-publish', 'Eigen WordPress content publiceren'],
        ['content-all', 'WordPress content van iedereen beheren'],
        ['content-admin', 'WordPress instellingen beheren'],

        // Create event permissions
        ['event-add', 'Evenementen toevoegen'],
        ['event-add-paid', 'Evenementen toevoegen (betaald)'],
        ['event-edit', 'Evenementen verwijderen'],
        ['event-delete', 'Evenementen verwijderen'],
        ['event-publish', 'Evenementen publiceren'],
        ['event-manage-all', 'Andermans evenementen bewerken'],

        // Create event user permissions
        ['event-view', 'Evenementen bekijken'],
        ['event-buy', 'Tickets voor evenementen kopen'],
        ['event-view-private', 'Evenementen bekijken (privé)'],
        ['event-buy-private', 'Tickets voor evenementen kopen (privé)'],

        // Generic permissions
        ['admin', 'Toegang tot admin panel'],
        ['devops', 'Toegang tot ops administratie'],
    ];

    /**
     * Roles, in [name, title, permissions[]] format
     *
     * @var array
     */
    protected $roles = [
        // Create guest role
        ['guest', 'Gast (standaard)', [
            // Allow viewing and buying event tickets
            'event-view',
            'event-buy'
        ]],

        // Standard members
        ['member', 'Gumbo Millennium lid', [
            // Allow file browsing
            'file-browse',
            'file-download',

            // Allow viewing and buying event tickets for private events
            'event-view-private',
            'event-buy-private'
        ]],

        // Activiteiten Committee
        ['ac', 'Activiteiten Commissie', [
            'admin',

            // Allow event management
            'event-add',
            'event-add-paid',
            'event-edit',
            'event-delete',
            'event-publish'
        ]],

        // Landhuis committee
        ['lhw', 'Landhuis Commissie', [
            'admin',

            // Allow event management
            'event-add',
            'event-add-paid',
            'event-edit',
            'event-delete',
            'event-publish'
        ]],

        // Public Relations Project Group
        ['pr', 'PRPG', [
            'admin',

            // Allow content management
            'content',
            'content-all'
        ]],

        // Board
        ['board', 'Bestuur', [
            // Allow file management
            'file-add',
            'file-edit',
            'file-delete',
            'file-publish',

            // Allow category management
            'file-category-add',
            'file-category-edit',
            'file-category-delete',

            // Allow event management
            'event-add',
            'event-add-paid',
            'event-edit',
            'event-delete',
            'event-publish',
            'event-manage-all',

            // Allow content management
            'content',
            'content-all'
        ]],

        // Digital committee
        ['dc', 'Digitale Commissie', 'all']
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');


        $permissionObjects = collect();
        $roleObjects = collect();

        // Create or update each permission, storing the resulting object in the permissionsObject collection
        foreach ($this->permissions as list($name, $title)) {
            $permissionObjects->put($name, Permission::updateOrCreate(
                ['name' => $name],
                ['title' => $title]
            ));
        }

        // Create or update each role and sync permissions
        foreach ($this->roles as list($name, $title, $permissions)) {
            $roleObject = Role::updateOrCreate(
                ['name' => $name],
                ['title' => $title]
            );

            // if permissions is 'all', assign all permissions
            if (is_string($permissions) && $permissions === 'all') {
                $rolePermissionObjects = Permission::all();
            } else {
                // Find all permissions for this object
                $rolePermissionObjects = collect();
                foreach ($permissions as $perm) {
                    // Only add permissions that actually exist
                    if ($permissionObjects->has($perm)) {
                        $rolePermissionObjects->push($permissionObjects->get($perm));
                    }
                }
            }

            // Sync all permissions, removing non-listed
            $roleObject->syncPermissions($rolePermissionObjects);

            // Store role object in list
            $roleObjects->put($name, $roleObject);
        }

        // Make 'guest' the default.
        Role::where('name', 'guest')
            ->update(['default' => 1]);

        // Remove 'default' role from all other roles, if any.
        Role::where('default', 1)
            ->where('name', '!=', 'guest')
            ->update(['default' => 0]);
    }
}
