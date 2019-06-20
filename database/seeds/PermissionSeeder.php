<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
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

        // Plaza cam permissions
        ['plazacam-view', 'Viewing of the plazacam (and coffee cam)'],
        ['plazacam-edit', 'Editing the plazacam (and coffee cam)'],

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

        // Manage sponsors
        ['sponsor-edit', 'Sponsoren bewerken'],

        // Manage roles
        ['user-role', 'Gebruikersrollen bewerken'],
        ['user-role-admin', 'Gebruikersrollen bewerken (admin)'],

        // Manage enrollments
        ['join-manage', 'Lidmaatschaps aanmeldingen beheren'],

        // Create event user permissions
        ['event-view', 'Evenementen bekijken'],
        ['event-buy', 'Tickets voor evenementen kopen'],
        ['event-view-private', 'Evenementen bekijken (privé)'],
        ['event-buy-private', 'Tickets voor evenementen kopen (privé)'],

        // Generic permissions
        ['payment-admin', 'Inzage in transacties'],
        ['admin', 'Toegang tot admin panel'],
        ['devops', 'Toegang tot ops administratie'],

        // Super Admin (only directly)
        ['super-admin', 'Super Admin'],
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
            'event-buy-private',

            // Allow watching the plazacam
            'plazacam-view',
        ]],

        // Activiteiten Committee
        ['ac', 'Activiteiten Commissie', [
            // Allow admin access
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
            // Allow admin access
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
            // Allow admin access
            'admin',

            // Allow content management
            'content',
            'content-all'
        ]],

        // Board
        ['board', 'Bestuur', [
            // Allow admin access
            'admin',

            // Allow file management
            'file-add',
            'file-edit',
            'file-delete',
            'file-publish',

            // Allow category management
            'file-category-add',
            'file-category-edit',
            'file-category-delete',

            // Allow sponsor manageent
            'sponsor-edit',

            // Allow permission management
            'user-role',

            // Allow event management
            'event-add',
            'event-add-paid',
            'event-edit',
            'event-delete',
            'event-publish',
            'event-manage-all',

            // Allow content management
            'content',
            'content-all',

            // Allow plazacam management
            'plazacam-edit',

            // Allow payment monitoring
            'payment-admin',
        ]],

        // Digital committee
        ['dc', 'Digitale Commissie', '*'],

        // Copied permissions from different groups
        ['ib', 'Intro Bestuur', 'ac'],
        ['bbqpg', 'BBQPG', 'ac'],
        ['bc', 'Brascommissie', 'ac'],
        ['intro-accie', 'Intro Accie', 'ac'],
        ['ib', 'Introbestuur', 'ac'],

        // Empty roles
        ['kc', 'Kascommissie', []],
        ['pc', 'Plazacommissie', []]
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

        // Get all permissions
        $allPermissions = Permission::all();

        // Create or update each role and sync permissions
        foreach ($this->roles as list($name, $title, $permissions)) {
            // Special string treatmet
            if (is_string($permissions)) {
                // Keep string
                $permissionsQuery = (string) $permissions;

                // Get all permissions
                $permissions = $allPermissions;

                // If the permissions isn't wildcard, look for a role with the same name
                if ($permissions !== '*') {
                    try {
                        $permissions = Role::findByName($permissionsQuery)->permissions;
                    } catch (RoleDoesNotExist $e) {
                        $permissions = [];
                    }
                }
            }

            // Convert collections
            if ($permissions instanceof Collection) {
                $permissions = $permissions->toArray();
            }

            if (!is_array($permissions)) {
                throw new \RuntimeException(sprintf(
                    'Permissions is not an array (it\'s a %s), whilst it should be one by now',
                    is_object($permissions) ? get_class($permissions) : gettype($permissions)
                ));
            }

            // Get role object
            $roleObject = Role::updateOrCreate(
                ['name' => $name],
                ['title' => $title]
            );

            // Get allowed permissions
            $rolePermissions = $permissionObjects->whereIn('name', array_wrap($permissions));

            // Sync all permissions, removing non-listed
            $roleObject->syncPermissions($rolePermissions);

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
