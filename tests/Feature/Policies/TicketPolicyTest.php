<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Models\Activity;
use App\Models\Role;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class TicketPolicyTest extends TestCase
{
    public static function provideUserTypes(): array
    {
        return [
            'logged out' => [null],
            'guest' => ['getGuestUser'],
            'member' => ['getMemberUser'],
            'commission' => ['getCommissionUser'],
            'admin' => ['getSuperAdminUser'],
        ];
    }

    /**
     * Test tickets inherit all permissions from the Activity model.
     *
     * @dataProvider provideUserTypes
     */
    public function test_inheriting_from_activity(?string $userType): void
    {
        $user = $userType ? $this->{$userType}() : null;

        $scopes = ['viewAny', 'view', 'create', 'update', 'delete'];
        $activity = Activity::factory()->withTickets()->create();
        $ticket = $activity->tickets->first();

        if ($user) {
            $this->actingAs($user);
        }

        foreach ($scopes as $scope) {
            $this->assertSame(Gate::allows($scope, $activity), Gate::allows($scope, $ticket), "Failed to check {$scope} on Activity matches Ticket");
        }
    }

    public function test_attaching_to_events(): void
    {
        $guestUser = $this->getGuestUser();
        $memberUser = $this->getMemberUser();
        $committeeUser = $this->getCommissionUser();
        $boardUser = $this->getBoardUser();
        $adminUser = $this->getSuperAdminUser();

        $activity = Activity::factory()->create([
            'role_id' => Role::findByName('ac')->id,
        ]);

        $this->assertFalse($guestUser->can('addTicket', $activity));
        $this->assertFalse($memberUser->can('addTicket', $activity));

        $this->assertTrue($committeeUser->can('addTicket', $activity));
        $this->assertTrue($boardUser->can('addTicket', $activity));
        $this->assertTrue($adminUser->can('addTicket', $activity));
    }
}
