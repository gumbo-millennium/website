<?php

declare(strict_types=1);

namespace Tests\Feature\Trails;

use App\Helpers\Arr;
use App\Helpers\Str;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\FormLayout;
use App\Models\States\Enrollment as States;
use App\Models\Ticket;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

/**
 * Test a full happy path of the enrollment process.
 */
class EnrollmentsTest extends TestCase
{
    public function test_enrollments_trail()
    {
        /** @var Faker $faker */
        $faker = App::make(Faker::class);

        /** @var Activity $activity */
        $activity = Activity::factory()
            ->withSeats()
            ->withForm()
            ->public()
            ->create();

        // Check for form
        if (empty($activity->form)) {
            $this->markTestSkipped('Cannot create form');
        }

        /** @var Ticket $firstTicket */
        [$firstTicket, $secondTicket] = $activity->tickets()->createMany([
            [
                'title' => 'Happy Trails Ticket',
                'price' => 25_00,
            ],
            [
                'title' => 'More Expensive Trail Ticket',
                'price' => 35_00,
            ],
        ]);

        // Check out the activity
        $this->get(route('activity.show', [$activity]))
            ->assertOk()
            ->assertSee($activity->title)
            ->assertSee(__('From :price', ['price' => Str::price($firstTicket->total_price)]));

        // Prep to enroll, which should require a login
        $this->get(route('enroll.create', [$activity]))
            ->assertRedirect(route('login'));

        // Login
        $user = User::factory()->create();
        $this->actingAs($user);

        // Try to enroll again
        $this->get(route('enroll.create', [$activity]))
            ->assertOk()
            ->assertSee($activity->title)
            ->assertSee($firstTicket->title)
            ->assertSee($secondTicket->title);

        // Save the enrollment
        $this->post(route('enroll.store', [$activity]), [
            'ticket_id' => $firstTicket->id,
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect($formUrl = route('enroll.form', [$activity]));

        // Ensure the enrollment was created
        /** @var Enrollment $enrollment */
        $enrollment = $activity->enrollments()->with(['user', 'ticket'])->first();
        $this->assertNotNull($enrollment);

        $this->assertTrue($user->is($enrollment->user));
        $this->assertTrue($firstTicket->is($enrollment->ticket));

        $this->assertInstanceOf(States\Created::class, $enrollment->state);
        $this->assertSame($firstTicket->total_price, $enrollment->price);

        // Check that the form view loads properly
        $this->get($formUrl)
            ->assertOk()
            ->assertSee(Arr::get($activity->enrollment_questions, '0.attributes.label'));

        // Prep a form
        $formField = $this->activity->form;
        $submitFields = [];

        /** @var FormLayout $field */
        foreach ($formField as $field) {
            $submitFields[$field->getName()] = $faker->sentence;
        }

        // Submit a form field
        $this->post(route('enroll.form.store', [$activity]), $submitFields)
            ->assertSessionHasNoErrors()
            ->assertRedirect($paymentUrl = route('enroll.pay', [$activity]));

        // Check form was assigned
        $enrollment->refresh();
        $this->assertInstanceOf(States\Seeded::class, $enrollment->state);
        $this->assertNotNull($enrollment->form);

        // Check pay page loads properly
        $this->get($paymentUrl)
            ->assertOk();
    }
}
