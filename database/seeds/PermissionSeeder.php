<?php

declare(strict_types=1);

use HJSON\HJSONException as HumanJsonException;
use HJSON\HJSONParser as HumanJsonParser;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Creates all roles required for ranks that users can have.
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class PermissionSeeder extends VerboseSeeder
{
    /**
     * Filename of the permission file, relative from the resources path
     */
    private const PERMISSION_FILE = 'assets/json/permissions.jsonc';

    /**
     * Filename of the roles file, relative from the resources path
     */
    private const ROLES_FILE = 'assets/json/roles.jsonc';

    /**
     * A human-friendly json parser
     * @var HumanJsonParser
     */
    public $jsonParser;

    /**
     * Preps a HumanJsonParser
     */
    public function __construct()
    {
        $this->jsonParser = new HumanJsonParser();
    }

    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // Load json files
        $permissionMap = $this->loadJson(self::PERMISSION_FILE);
        $roleMap = $this->loadJson(self::ROLES_FILE);

        if (!$permissionMap) {
            logger()->error("Cannot read permission json map, please check [file].", ['file' => self::PERMISSION_FILE]);
            $this->error('Cannot read permission json map, please check %s.', self::PERMISSION_FILE);
            return false;
        }
        if (!$roleMap) {
            logger()->error("Cannot read roles json map, please check [file].", ['file' => self::PERMISSION_FILE]);
            $this->error('Cannot read roles json map, please check %s.', self::ROLES_FILE);
            return false;
        }

        // Seed permissions
        $permissions = $this->seedPermissions($permissionMap);

        // Load roles file
        $this->seedRoles($permissions, $roleMap);
    }

    /**
     * Creates all permissions in the $permissions collection, and returns a
     * collection filled with Permission elements, based on name
     * @param Collection $permissionMap
     * @return Collection
     */
    public function seedPermissions(Collection $permissionMap): Collection
    {
        // Collection of created permission
        $collection = collect();

        // Loop through permission
        foreach ($permissionMap as $name => $title) {
            // Creates or updates the given permission
            $perm = Permission::updateOrCreate(
                compact('name'),
                compact('title')
            );

            // Assign item
            $collection->put($name, $perm);
        }

        // Returns collection
        return $collection;
    }

    /**
     * Creates a map with permissons to assign to roles
     * @param Collection $permissions
     * @param Collection $roles
     * @param Collection $roleMap
     * @return Collection
     */
    public function mapRolePermissions(Collection $permissions, Collection $roles, Collection $roleMap): Collection
    {
        // Intermediate map
        $rolePermissionMap = collect();

        // Map first-party permissions to the name
        foreach ($roleMap as $name => $roleData) {
            // Get title and permissions
            $wantedPermissons = Arr::get($roleData, 'permissions', []);

            // Add all permissions, if requested
            if (count($wantedPermissons) === 1 && Arr::first($wantedPermissons) === '*') {
                $rolePermissionMap->put($name, $permissions);

                logger()->info(
                    "Updated [role] with wildcard, it now has [count] permissions.",
                    [
                    'role' => $roles->get($name) ?? $name,
                    'count' => count($rolePermissionMap->get($name))
                    ]
                );
                continue;
            }

            $rolePermissionMap->put($name, $permissions->only($wantedPermissons));

            logger()->info(
                "Updated [role], it now has [count] permissions.",
                [
                    'role' => $roles->get($name) ?? $name,
                    'count' => count($rolePermissionMap->get($name))
                ]
            );
        }

        // Map extended permissions to the name too
        foreach ($roleMap as $name => $data) {
            $extendName = Arr::get($data, 'extends', null);
            // Skip if there's no extension
            if (empty($extendName)) {
                continue;
            }

            // Update permission for this name
            $rolePermissionMap->put($name, $updatedPermissions = collect([
                $rolePermissionMap->get($extendName),
                $rolePermissionMap->get($name),
            ])->flatten());

            logger()->info(
                "Role [role] extends [source-role], now has [count] permissions",
                [
                'role' => $roles->get($name) ?? $name,
                'source-role' => $roles->get($extendName) ?? $extendName,
                'count' => $updatedPermissions->count()
                ]
            );
        }

        // Return the permissionmap
        return $rolePermissionMap;
    }

    /**
     * Creats all roles and assings permissions
     * @param Collection $permissions
     * @param Collection $roleMap
     * @return Collection
     */
    public function seedRoles(Collection $permissions, Collection $roleMap): Collection
    {
        // Prep a roles collection
        $roles = collect();

        // Generate or update all permissions
        foreach ($roleMap as $name => $roleData) {
            // Get role title
            $title = Arr::get($roleData, 'title');

            // Get default flag (0 by default)
            $default = Arr::get($roleData, 'default') ? 1 : 0;

            // Add to the role queue, by either updating or creating the role
            $roles->put($name, $role = Role::updateOrCreate(
                compact('name'),
                compact('title', 'default')
            ));

            // Log result
            logger()->info(sprintf(
                "%s role [role].",
                $role->wasRecentlyCreated ? 'Created' : 'Updated',
            ), [
                'role' => $role ?? $name,
            ]);
        }

        // Generate role ⋄ permissions
        $rolePermissionMap = $this->mapRolePermissions($permissions, $roles, $roleMap);

        // Link rolePermissionMap to the Role
        foreach ($roles as $name => $role) {
            if ($rolePermissionMap->has($name)) {
                // Update permissions
                $role->syncPermissions($rolePermissionMap->get($name));

                // Refresh model (for updated counts)
                $role->refresh();

                // Log result
                logger()->info(
                    "Role [role] was updated, now has [count] permissions",
                    [
                    'role' => $roles->get($name) ?? $name,
                    'count' => $role->permissions()->count()
                    ]
                );
            }
        }

        // Return roles
        return $roles;
    }

    /**
     * Returns JSON from file, as Collection
     * @param string $path
     * @return Collection|null
     */
    private function loadJson(string $path): ?Collection
    {
        // Get path of the file
        $fullPath = resource_path($path);
        if (!file_exists($fullPath)) {
            return null;
        }

        // Get contents of the file
        $contents = file_get_contents($fullPath);

        // Convert contents to collection
        try {
            return collect($this->jsonParser->parse($contents, [
                'assoc' => true
            ]));
        } catch (HumanJsonException $e) {
            return null;
        }
    }
}
