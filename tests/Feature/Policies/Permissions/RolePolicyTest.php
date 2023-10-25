<?php

declare(strict_types=1);

namespace Tests\Feature\Policies\Permissions;

use App\Models\Role;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tests\Traits\RefreshDatabase;

class RolePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_normal_user(): void
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $role = Role::create(['name' => 'test']);
        $permission = Permission::create(['name' => 'test']);

        $this->assertFalse($user->can('viewAny', Role::class));
        $this->assertFalse($user->can('view', $role));
        $this->assertFalse($user->can('create', Role::class));
        $this->assertFalse($user->can('update', $role));
        $this->assertFalse($user->can('delete', $role));

        $this->assertFalse($user->can('attachUser', [$role, $user2]));
        $this->assertFalse($user->can('detachUser', [$role, $user2]));

        $this->assertFalse($user->can('attachPermission', [$role, $permission]));
        $this->assertFalse($user->can('detachPermission', [$role, $permission]));
    }

    public function test_role_managing_user(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('role');

        $user2 = User::factory()->create();
        $role = Role::create(['name' => 'test']);
        $permission = Permission::create(['name' => 'test']);

        $this->assertTrue($user->can('viewAny', Role::class));
        $this->assertTrue($user->can('view', $role));
        $this->assertFalse($user->can('create', Role::class));
        $this->assertFalse($user->can('update', $role));
        $this->assertFalse($user->can('delete', $role));

        $this->assertFalse($user->can('attachUser', [$role, $user2]));
        $this->assertFalse($user->can('detachUser', [$role, $user2]));

        $this->assertFalse($user->can('attachPermission', [$role, $permission]));
        $this->assertFalse($user->can('detachPermission', [$role, $permission]));
    }

    public function test_role_admin_user(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['role', 'role-admin']);

        $user2 = User::factory()->create();
        $role = Role::create(['name' => 'test']);
        $permission = Permission::create(['name' => 'test']);

        $this->assertTrue($user->can('viewAny', Role::class));
        $this->assertTrue($user->can('view', $role));
        $this->assertFalse($user->can('create', Role::class));
        $this->assertFalse($user->can('update', $role));
        $this->assertFalse($user->can('delete', $role));

        $this->assertTrue($user->can('attachUser', [$role, $user2]));
        $this->assertTrue($user->can('detachUser', [$role, $user2]));

        $this->assertFalse($user->can('attachPermission', [$role, $permission]));
        $this->assertFalse($user->can('detachPermission', [$role, $permission]));
    }
}
