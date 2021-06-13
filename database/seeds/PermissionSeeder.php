<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Creates all roles required for ranks that users can have.
 */
class PermissionSeeder extends VerboseSeeder
{
    /**
     * Filename of the permission file, relative from the resources path.
     */
    private const PERMISSION_FILE = 'yaml/permissions.yaml';

    /**
     * Filename of the roles file, relative from the resources path.
     */
    private const ROLES_FILE = 'yaml/roles.yaml';

    /**
     * Run the database seeds.
     *
     * @return bool
     */
    public function run()
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Load json files
        $permissionMap = $this->readYaml(self::PERMISSION_FILE);
        $roleMap = $this->readYaml(self::ROLES_FILE);

        if (! $permissionMap) {
            Log::error('Cannot read permission json map, please check {file}.', ['file' => self::PERMISSION_FILE]);
            $this->error('Cannot read permission json map, please check %s.', self::PERMISSION_FILE);

            return false;
        }
        if (! $roleMap) {
            Log::error('Cannot read roles json map, please check {file}.', ['file' => self::PERMISSION_FILE]);
            $this->error('Cannot read roles json map, please check %s.', self::ROLES_FILE);

            return false;
        }

        // Seed permissions
        $permissions = $this->seedPermissions($permissionMap);

        // Load roles file
        $this->seedRoles($permissions, $roleMap);

        return true;
    }

    /**
     * Creates all permissions in the $permissions collection, and returns a
     * collection filled with Permission elements, based on name.
     */
    public function seedPermissions(Collection $permissionMap): Collection
    {
        // Collection of created permission
        $collection = collect();

        // Loop through permission
        foreach ($permissionMap as $name => $title) {
            // Creates or updates the given permission
            $perm = Permission::query()
                ->updateOrCreate(
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
     * Creates a map with permissons to assign to roles.
     *
     * @param Collection<Permission> $permissions
     * @param Collection<Role> $roles
     * @param Collection<array> $roleMap
     */
    public function mapRolePermissions(
        Collection $permissions,
        Collection $roles,
        Collection $roleMap
    ): Collection {
        // Intermediate map
        $rolePermissionMap = collect();

        // Map first-party permissions to the name
        foreach ($roleMap as $name => $roleData) {
            // Get title and permissions
            $wantedPermissions = Arr::get($roleData, 'permissions', []);

            // Add all permissions, if requested
            if (count($wantedPermissions) === 1 && Arr::first($wantedPermissions) === '*') {
                $rolePermissionMap->put($name, $permissions);

                Log::info('Updated {role} with wildcard, it now has {count} permissions.', [
                    'role' => $roles->get($name) ?? $name,
                    'count' => count($rolePermissionMap->get($name)),
                ]);

                continue;
            }

            $rolePermissionMap->put($name, $permissions->only($wantedPermissions));

            Log::info('Updated {role}, it now has {count} permissions.', [
                'role' => $roles->get($name) ?? $name,
                'count' => count($rolePermissionMap->get($name)),
            ]);
        }

        // Map extended permissions to the name too
        foreach ($roleMap as $name => $data) {
            $extendName = Arr::get($data, 'extends');
            // Skip if there's no extension
            if (empty($extendName)) {
                continue;
            }

            // Update permission for this name
            $rolePermissionMap->put($name, $updatedPermissions = collect([
                $rolePermissionMap->get($extendName),
                $rolePermissionMap->get($name),
            ])->flatten());

            Log::info('Role {role} extends {source-role}, now has {count} permissions', [
                'role' => $roles->get($name) ?? $name,
                'source-role' => $roles->get($extendName) ?? $extendName,
                'count' => $updatedPermissions->count(),
            ]);
        }

        // Return the permissionmap
        return $rolePermissionMap;
    }

    /**
     * Creats all roles and assings permissions.
     *
     * @param Collection<Permission> $permissions
     * @param Collection<Role> $roleMap
     * @return Collection<Role>
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
            $roles->put($name, $role = Role::query()->updateOrCreate(
                compact('name'),
                compact('title', 'default')
            ));

            // Log result
            $action = $role->wasRecentlyCreated ? 'Created' : 'Updated';
            Log::info("${action} role {role}.", [
                'role' => $role ?? $name,
            ]);
        }

        // Generate role ⋄ permissions
        $rolePermissionMap = $this->mapRolePermissions($permissions, $roles, $roleMap);

        // Link rolePermissionMap to the Role
        foreach ($roles as $name => $role) {
            if (! $rolePermissionMap->has($name)) {
                continue;
            }

            // Update permissions
            $role->permissions()->sync($rolePermissionMap->get($name)->pluck('id'));

            // Log result
            Log::info('Role {role} was updated, now has {count} permissions', [
                'role' => $roles->get($name) ?? $name,
                'count' => $role->permissions()->count(),
            ]);
        }

        // Return roles
        return $roles;
    }

    /**
     * Returns JSON from file, as Collection.
     */
    private function readYaml(string $path): ?Collection
    {
        // Get path of the file
        $fullPath = resource_path($path);
        if (! file_exists($fullPath)) {
            return null;
        }

        // Get contents of the file
        try {
            return collect(
                Yaml::parseFile($fullPath)
            );
        } catch (ParseException $parseException) {
            optional($this->command)->line(sprintf(
                '<error>Error</>: Failed to read <info>%s</>: %s',
                $path,
                $parseException->getMessage()
            ));

            report(new RuntimeException(sprintf(
                'Failed to read <info>%s</>: %s',
                $path,
                $parseException->getMessage()
            ), 0, $parseException));

            return null;
        }
    }
}
