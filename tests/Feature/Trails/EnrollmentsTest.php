<?php

declare(strict_types=1);

namespace Tests\Feature\Trails;

use App\Helpers\Arr;
use App\Helpers\Str;
use App\Nova\Flexible\Layouts;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\FormLayout;
use App\Models\States\Enrollment as States;
use App\Models\Ticket;
use App\Models\User;
use Generator;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use LogicException;
use Tests\TestCase;

/**
 * Test a full happy path of the enrollment process.
 */
class EnrollmentsTest extends TestCase
{
    use WithFaker;

    public function test_enrollments_trail()
    {
        /** @var Activity $activity */
        $activity = Activity::factory()
            ->withSeats()
            ->public()
            ->create([
                'enrollment_questions' => [...$this->getForm()],
            ]);

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
            ->assertSee(
                $activity->tickets
                    ->map(fn (Ticket $ticket) => Str::price($ticket->total_price))
                    ->implode(' of '),
            );

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
            ->assertRedirect($showUrl = route('enroll.show', $activity));

        // Ensure the enrollment was created
        /** @var Enrollment $enrollment */
        $enrollment = $activity->enrollments()->with(['user', 'ticket'])->first();
        $this->assertNotNull($enrollment);

        $this->assertTrue($user->is($enrollment->user));
        $this->assertTrue($firstTicket->is($enrollment->ticket));

        $this->assertInstanceOf(States\Created::class, $enrollment->state);
        $this->assertSame($firstTicket->total_price, $enrollment->total_price);

        // Check that the "show" page now redirects to the form
        $this->get($showUrl)
            ->assertRedirect($formUrl = route('enroll.form', $activity));

        // Check that the form view loads properly
        $this->get($formUrl)
            ->assertOk()
            ->assertSee(Arr::get($activity->enrollment_questions, '0.attributes.label'));

        // Prep a form
        $submitFields = Collection::make($activity->form)
            ->map->getName()
            ->combine([
                'Text Field',
                'email@example.com',
                '+31 6 12345678',
                'Textarea Field v9001',
            ])
            ->put('accept-terms', 'yes')
            ->toArray();

        // Submit a form field
        $this->put(route('enroll.formStore', [$activity]), $submitFields)
            ->assertSessionHasNoErrors()
            ->assertRedirect($showUrl);

        // Check form was assigned
        $enrollment->refresh();
        $this->assertInstanceOf(States\Seeded::class, $enrollment->state);
        $this->assertNotNull($enrollment->form);

        // Ensure show redirects to the payment page
        $this->get($showUrl)
            ->assertRedirect($paymentUrl = route('enroll.pay', $activity));

        // Check pay page loads properly
        $this->get($paymentUrl)
            ->assertOk();
    }

    private function getForm(): Generator
    {
        $fields = [
            Layouts\FormField::class,
            Layouts\FormEmail::class,
            Layouts\FormPhone::class,
            Layouts\FormTextArea::class,
        ];

        foreach ($fields as $field) {
            yield [
                'key' => Str::random(16),
                'layout' => (new $field)->name(),
                "attributes" => [
                    "help" => $this->faker->sentence(),
                    "label" => $this->faker->sentence(),
                    "required" => true
                ]
            ];
        }
    }
}
