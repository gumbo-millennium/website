<?php

declare(strict_types=1);

namespace Tests\Feature\Policies\Permissions;

use App\Models\Role;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tests\Traits\RefreshDatabase;

class PermissionPolicyTest extends TestCase
{
    use RefreshDatabase;

    public static function roleProvider(): array
    {
        return [
            'no roles' => [[], false],
            'management role' => [['role'], true],
            'admin role' => [['role', 'role-admin'], true],
        ];
    }

    /**
     * @dataProvider roleProvider
     */
    public function test_permissions_are_locked(array $roles, bool $canSeePermissions): void
    {
        $user = User::factory()->create();
        $roles and $user->givePermissionTo($roles);

        $user2 = User::factory()->create();
        $permission = Permission::create(['name' => 'test']);
        $role = Role::create(['name' => 'test']);

        $this->assertSame($canSeePermissions, $user->can('viewAny', Permission::class));
        $this->assertSame($canSeePermissions, $user->can('view', $permission));

        $this->assertFalse($user->can('create', Permission::class));
        $this->assertFalse($user->can('update', $permission));
        $this->assertFalse($user->can('delete', $permission));

        $this->assertFalse($user->can('attachUser', [$permission, $user2]));
        $this->assertFalse($user->can('detachUser', [$permission, $user2]));

        $this->assertFalse($user->can('attachRole', [$permission, $role]));
        $this->assertFalse($user->can('detachRole', [$permission, $role]));
    }
}
