<?php

use HJSON\HJSONException as HumanJsonException;
use HJSON\HJSONParser as HumanJsonParser;
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
     * Filename of the permission file, relative from the resources path
     * @var string
     */
    private const PERMISSION_FILE = 'assets/json/permissions.jsonc';

    /**
     * Filename of the roles file, relative from the resources path
     * @var string
     */
    private const ROLES_FILE = 'assets/json/roles.jsonc';

    /**
     * A human-friendly json parser
     *
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
     *
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
            printf("Cannot read permission json map, please check %s.\n", self::PERMISSION_FILE);
            return false;
        }
        if (!$roleMap) {
            printf("Cannot read roles json map, please check %s.\n", self::ROLES_FILE);
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
     *
     * @param Collection $permissionMap
     * @return Collection
     */
    public function seedPermissions(Collection $permissionMap) : Collection
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
     *
     * @param Collection $permissions
     * @param Collection $roles
     * @param Collection $roleMap
     * @return Collection
     */
    public function mapRolePermissions(Collection $permissions, Collection $roles, Collection $roleMap) : Collection
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

                printf(
                    "Role %s has wildcard, now has %d permissions\n",
                    optional($roles->get($name))->title ?? $name,
                    count($rolePermissionMap->get($name))
                );
                continue;
            }

            $rolePermissionMap->put($name, $permissions->only($wantedPermissons));

            printf(
                "Role %s has %d permissions\n",
                optional($roles->get($name))->title ?? $name,
                count($rolePermissionMap->get($name))
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

            printf(
                "Role %s extends %s, now has %d permissions\n",
                optional($roles->get($name))->title ?? $name,
                optional($roles->get($extendName))->title ?? $extendName,
                $updatedPermissions->count()
            );
        }

        // Return the permissionmap
        return $rolePermissionMap;
    }

    /**
     * Creats all roles and assings permissions
     *
     * @param Collection $permissions
     * @param Collection $roleMap
     * @return Collection
     */
    public function seedRoles(Collection $permissions, Collection $roleMap) : Collection
    {
        // Prep a roles collection
        $roles = collect();

        // Generate or update all permissions
        foreach ($roleMap as $name => $roleData) {
            $title = Arr::get($roleData, 'title');
            $default = Arr::get($roleData, 'default') ? 1 : 0;
            $roles->put($name, $role = Role::updateOrCreate(
                compact('name'),
                compact('title', 'default')
            ));

            printf(
                "%s role %s.\n",
                $role->wasRecentlyCreated ? 'Created' : 'Updated',
                $role->title ?? $name,
            );
        }

        // Generate role ⋄ permissions
        $rolePermissionMap = $this->mapRolePermissions($permissions, $roles, $roleMap);

        // Link rolePermissionMap to the Role
        foreach ($roles as $name => $role) {
            if ($rolePermissionMap->has($name)) {
                $role->syncPermissions($rolePermissionMap->get($name));
                printf(
                    "Role %s now has %d permissions\n",
                    $role->title ?? $name,
                    count($rolePermissionMap->get($name))
                );
            }
        }

        // Return roles
        return $roles;
    }

    /**
     * Returns JSON from file, as Collection
     *
     * @param string $path
     * @return Collection|null
     */
    private function loadJson(string $path) : ?Collection
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
