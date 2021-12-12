<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Models\RedirectInstruction;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class RedirectInstructionPolicyTest extends TestCase
{
    public function test_guest(): void
    {
        $redirect = RedirectInstruction::factory()->create();
        assert($redirect instanceof RedirectInstruction);

        $this->assertFalse(Gate::allows('viewAny', $redirect));
        $this->assertFalse(Gate::allows('view', $redirect));
        $this->assertFalse(Gate::allows('create', $redirect));
        $this->assertFalse(Gate::allows('update', $redirect));
        $this->assertFalse(Gate::allows('delete', $redirect));
        $this->assertFalse(Gate::allows('restore', $redirect));
        $this->assertFalse(Gate::allows('forceDelete', $redirect));
    }

    /**
     * @dataProvider userProvider
     * @param array|string $roleOrRoles
     */
    public function test_user($roleOrRoles, bool $canView, bool $canEdit): void
    {
        $redirect = RedirectInstruction::factory()->create();
        assert($redirect instanceof RedirectInstruction);

        $user = User::factory()->create()->assignRole($roleOrRoles);
        assert($user instanceof User);
        $this->actingAs($user);

        $this->assertSame($canView, Gate::allows('viewAny', $redirect));
        $this->assertSame($canView, Gate::allows('view', $redirect));
        $this->assertSame($canEdit, Gate::allows('create', $redirect));
        $this->assertSame($canEdit, Gate::allows('update', $redirect));
        $this->assertSame($canEdit, Gate::allows('delete', $redirect));
        $this->assertSame($canEdit, Gate::allows('restore', $redirect));
        $this->assertSame(false, Gate::allows('forceDelete', $redirect));
    }

    public function userProvider(): array
    {
        return [
            'no-roles' => [[], false, false],
            'restricted' => ['restricted', false, false],
            'verified' => ['verified', false, false],
            'guest' => ['guest', false, false],

            // Start of read-only access
            'member' => ['member', true, false],
            'ac' => ['ac', true, false],
            'lhw' => ['lhw', true, false],
            'pr' => ['pr', true, false],

            // Start of admins
            'board' => ['board', true, true],
            'dc' => ['dc', true, true],
        ];
    }
}
