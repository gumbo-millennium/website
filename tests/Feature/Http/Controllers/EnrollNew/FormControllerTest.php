<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\EnrollNew;

use App\Facades\Enroll;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment as States;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * Test cases:.
 *
 * ✅ guest access
 * ✅ no enrolled access
 * - access without form
 * - access with form
 * - submit without form
 * - submit without data
 * - submit with invalid data
 * - submit with valid data
 * - re-visit
 */
class FormControllerTest extends TestCase
{
    public function test_guest_access(): void
    {
        $activity = factory(Activity::class)->states(['with-form'])->create();

        $this->get(route('enroll.form', $activity))
            ->assertRedirect(route('login'));

        $this->get(route('enroll.formStore', $activity))
            ->assertRedirect(route('login'));
    }

    public function test_not_enrolled_access(): void
    {
        $activity = factory(Activity::class)->states(['with-form', 'with-tickets'])->create();
        $ticket = $activity->tickets->first();

        $this->actingAs(factory(User::class)->create());

        $this->get(route('enroll.form', $activity))
            ->assertRedirect(route('enroll.create', $activity));

        $this->get(route('enroll.formStore', $activity))
            ->assertRedirect(route('enroll.create', $activity));

        $enrollment = Enroll::createEnrollment($activity, $ticket);
        $enrollment->state = States\Cancelled::class;
        $enrollment->save();

        $this->get(route('enroll.form', $activity))
            ->assertRedirect(route('enroll.create', $activity));

        $this->get(route('enroll.formStore', $activity))
            ->assertRedirect(route('enroll.create', $activity));
    }

    public function test_proper_access(): void
    {
        /** @var Activity $activity */
        $activity = factory(Activity::class)->states('with-form', 'with-tickets')->create();
        $ticket = $activity->tickets->first();

        if (Collection::make($activity->form)->isEmpty()) {
            $this->markTestSkipped('No form for this activity.');
        }

        $this->actingAs(factory(User::class)->create());

        /** @var Enrollment $enrollment */
        $enrollment = Enroll::createEnrollment($activity, $ticket);
        $this->assertNotNull($enrollment);
        $this->assertInstanceOf(States\Created::class, $enrollment->state);

        Collection::make($activity->form);

        $this->get(route('enroll.form', $activity))
            ->assertOk();

        // Collection::make($activity->form)
        //     ->map->getName();
    }

    public function test_without_form(): void
    {
        /** @var Activity $activity */
        $activity = factory(Activity::class)->states('with-form', 'with-tickets')->create();
        $ticket = $activity->tickets->first();

        $this->actingAs(factory(User::class)->create());
        $enrollment = Enroll::createEnrollment($activity, $ticket);

        $this->assertNotNull($enrollment);

        $this->get(route('enroll.form', $activity))
            ->assertRedirect(route('enroll.show', $activity));

        $this->put(route('enroll.formStore', $activity))
            ->assertStatus(Response::HTTP_BAD_REQUEST);
    }
}
