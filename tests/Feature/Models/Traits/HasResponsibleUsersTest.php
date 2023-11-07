<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Traits;

use App\Models\Activity;
use App\Models\User;
use Tests\TestCase;

/**
 * Tests the HasResponsibleUsers trait with the Activity model.
 */
class HasResponsibleUsersTest extends TestCase
{
    /**
     * Tests the handles if no user is acting upon stuff.
     */
    public function test_system_level_changes(): void
    {
        $activity = Activity::factory()->create();

        $this->assertNull($activity->created_by_id);
        $this->assertNull($activity->created_by);

        $this->assertNull($activity->updated_by_id);
        $this->assertNull($activity->updated_by);

        $this->travel(5)->minutes();

        $activity->name = 'Test 123';

        $oldChanged = $activity->updated_at;
        $activity->save();

        $this->assertNotEquals($oldChanged, $activity->updated_at);

        $this->assertNull($activity->created_by_id);
        $this->assertNull($activity->created_by);

        $this->assertNull($activity->updated_by_id);
        $this->assertNull($activity->updated_by);
    }

    /**
     * Tests the handles if a user is switched between the create and edit.
     */
    public function test_with_users(): void
    {
        [$user1, $user2] = User::factory(2)->create();

        $this->actingAs($user1);

        $activity = Activity::factory()->create();

        $this->assertEquals($user1->id, $activity->created_by_id);
        $this->assertTrue($user1->is($activity->created_by));

        $this->assertEquals($user1->id, $activity->updated_by_id);
        $this->assertTrue($user1->is($activity->updated_by));

        $this->travel(5)->minutes();
        $this->actingAs($user2);

        $activity->name = 'Test 123';

        $oldChanged = $activity->updated_at;
        $activity->save();

        $this->assertNotEquals($oldChanged, $activity->updated_at);

        $this->assertEquals($user1->id, $activity->created_by_id);
        $this->assertTrue($user1->is($activity->created_by));

        $this->assertEquals($user2->id, $activity->updated_by_id);
        $this->assertTrue($user2->is($activity->updated_by));
    }

    public function test_create_is_not_touched_on_updates(): void
    {
        $user = User::factory()->create();

        $activity = Activity::factory()->create();

        $this->assertNull($activity->created_by_id);
        $this->assertNull($activity->created_by);

        $this->assertNull($activity->updated_by_id);
        $this->assertNull($activity->updated_by);

        $this->travel(5)->minutes();
        $this->actingAs($user);

        $activity->name = 'Test 123';

        $oldChanged = $activity->updated_at;
        $activity->save();

        $this->assertNotEquals($oldChanged, $activity->updated_at);

        $this->assertNull($activity->created_by_id);
        $this->assertNull($activity->created_by);

        $this->assertEquals($user->id, $activity->updated_by_id);
        $this->assertTrue($user->is($activity->updated_by));
    }
}
