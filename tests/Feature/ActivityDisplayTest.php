<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Facades\Enroll;
use App\Models\Activity;
use App\Models\States\Enrollment\Confirmed;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class ActivityDisplayTest extends TestCase
{
    /**
     * Get test route.
     */
    public function test_various_routes(): void
    {
        /** @var Activity $publicEvent */
        $publicEvent = Activity::factory()->create([
            'is_public' => true,
            'published_at' => Date::today()->subYear(),
            'start_date' => Date::today()->addWeeks(1),
            'end_date' => Date::today()->addWeeks(1)->addHours(5),
        ]);

        /** @var Activity $privateEvent */
        $privateEvent = Activity::factory()->create([
            'is_public' => false,
            'published_at' => Date::today()->subYear(),
            'start_date' => Date::today()->addWeeks(2),
            'end_date' => Date::today()->addWeeks(2)->addHours(5),
        ]);

        $this->get(route('activity.show', $publicEvent))
            ->assertOk()
            ->assertSee($publicEvent->name)
            ->assertSee('data-action="enroll"', false);

        $this->get(route('activity.show', $privateEvent))
            ->assertRedirect(route('login'));

        $this->get(route('activity.index'))
            ->assertSeeText($publicEvent->name)
            ->assertDontSeeText($privateEvent->name);

        $this->actingAs($this->getGuestUser());

        $this->get(route('activity.show', $publicEvent))
            ->assertOk()
            ->assertSee($publicEvent->name)
            ->assertSee('data-action="enroll"', false);

        $this->get(route('activity.show', $privateEvent))
            ->assertForbidden();

        $this->get(route('activity.index'))
            ->assertSeeText($publicEvent->name)
            ->assertDontSeeText($privateEvent->name);

        $this->actingAs($this->getMemberUser());

        $this->get(route('activity.show', $publicEvent))
            ->assertOk()
            ->assertSee($publicEvent->name)
            ->assertSee('data-action="enroll"', false);

        $this->get(route('activity.show', $privateEvent))
            ->assertOk()
            ->assertSee($privateEvent->name)
            ->assertSee('data-action="enroll"', false);

        $this->get(route('activity.index'))
            ->assertSeeText($publicEvent->name)
            ->assertSeeText($privateEvent->name);
    }

    public function test_activity_enrollment_button(): void
    {
        $this->actingAs($user = User::factory()->create());

        $activity = Activity::factory()->withTickets()->create();
        $pastActivity = Activity::factory()->withTickets()->create([
            'start_date' => Date::now()->subWeek(),
            'end_date' => Date::now()->subWeek()->addHour(),
        ]);

        // Get future activity
        $this->get(route('activity.show', $activity))
            ->assertOk()
            ->assertSee('data-action="enroll"', false)
            ->assertDontSee('data-action="view-enrollment"', false)
            ->assertDontSee('data-action="transfer-enrollment"', false);

        // Get past activity page and ensure the enrollment button isn't shown
        $this->get(route('activity.show', $pastActivity))
            ->assertOk()
            ->assertDontSee('data-action="enroll"', false)
            ->assertDontSee('data-action="view-enrollment"', false)
            ->assertDontSee('data-action="transfer-enrollment"', false);

        // Make an enrollment
        $enrollment = Enroll::createEnrollment($activity, $activity->tickets->first());

        // Ensure the state is right
        $this->assertFalse($enrollment->is_stable, 'Created enrollment is in a stable state, which is wrong');

        $this->get(route('activity.show', $activity))
            ->assertOk()
            ->assertDontSee('data-action="enroll"', false)
            ->assertSee('data-action="view-enrollment"', false)
            ->assertDontSee('data-action="transfer-enrollment"', false);

        // Make the enrollment stable
        $enrollment->transitionTo(Confirmed::class);
        $enrollment->refresh();

        // Ensure the state is right
        $this->assertTrue($enrollment->is_stable, 'Created enrollment is not in a stable state, which is wrong');

        // Ensure the right buttons are shown
        $this->get(route('activity.show', $activity))
            ->assertOk()
            ->assertDontSee('data-action="enroll"', false)
            ->assertSee('data-action="view-enrollment"', false)
            ->assertSee('data-action="transfer-enrollment"', false);
    }
}
