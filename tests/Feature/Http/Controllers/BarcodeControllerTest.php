<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Facades\Enroll;
use App\Http\Controllers\BarcodeController;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment as States;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class BarcodeControllerTest extends TestCase
{
    public function test_unauthorized_access_request(): void
    {
        $memberUser = $this->getMemberUser();
        $boardUser = $this->getBoardUser();

        $activity = Activity::factory()->withTickets()->create([
            'end_date' => Date::now()->addDays(1),
        ]);
        $ticket = $activity->tickets()->first();

        $enrollment = $activity->enrollments()->save(
            Enrollment::factory()
                ->for($memberUser)
                ->for($ticket)
                ->make([
                    'state' => States\Confirmed::class,
                ]),
        );

        $indexRoute = route('barcode.index');
        $showRoute = route('barcode.show', $activity);
        $preloadRoute = route('barcode.preload', $activity);
        $consumeRoute = route('barcode.consume', $activity);

        $this->get($indexRoute)->assertRedirect(route('login'));
        $this->get($showRoute)->assertRedirect(route('login'));

        $this->getJson($preloadRoute)->assertUnauthorized();
        $this->postJson($consumeRoute, ['barcode' => $enrollment->ticket_code])
            ->assertUnauthorized();

        $this->actingAs($memberUser);

        $this->get($indexRoute)->assertForbidden();
        $this->get($showRoute)->assertForbidden();

        $this->getJson($preloadRoute)->assertForbidden();
        $this->postJson($consumeRoute, ['barcode' => $enrollment->ticket_code])
            ->assertForbidden();

        $this->actingAs($boardUser);

        $this->get($indexRoute)->assertOk();
        $this->get($showRoute)->assertOk();

        $this->getJson($preloadRoute)->assertOk();
        $this->postJson($consumeRoute, ['barcode' => $enrollment->ticket_code])
            ->assertOk();
    }

    public function test_activity_scoping(): void
    {
        $currentActivity = Activity::factory()->withTickets()->create([
            'start_date' => Date::now()->subHour(),
            'end_date' => Date::now()->addHours(6),
        ]);

        $futureActivity = Activity::factory()->withTickets()->create([
            'start_date' => Date::now()->addDay(),
            'end_date' => Date::now()->addDay()->addHours(7),
        ]);

        $pastActivity = Activity::factory()->withTickets()->create([
            'start_date' => Date::now()->subDays(1)->subHour(),
            'end_date' => Date::now()->subDays(1),
        ]);

        $cancelledActivity = Activity::factory()->withTickets()->create([
            'start_date' => Date::now()->subHour(),
            'end_date' => Date::now()->addHours(6),
            'cancelled_at' => Date::now(),
        ]);

        $activityWithoutTickets = Activity::factory()->create([
            'start_date' => Date::now()->subHour(),
            'end_date' => Date::now()->addHours(6),
        ]);

        $this->actingAs($this->getBoardUser());

        $this->get(route('barcode.index'))
            ->assertSeeInOrder([
                $currentActivity->name,
                $futureActivity->name,
            ])
            ->assertDontSee($pastActivity->name)
            ->assertDontSee($cancelledActivity->name)
            ->assertDontSee($activityWithoutTickets->name);

        $this->get(route('barcode.show', $currentActivity))->assertOk();
        $this->get(route('barcode.show', $futureActivity))->assertOk();
        $this->get(route('barcode.show', $pastActivity))->assertRedirect(route('barcode.index'));
        $this->get(route('barcode.show', $cancelledActivity))->assertRedirect(route('barcode.index'));
        $this->get(route('barcode.show', $activityWithoutTickets))->assertRedirect(route('barcode.index'));
    }

    public function test_preload(): void
    {
        [$activity, $otherActivity] = Activity::factory(2)->withTickets()->create();

        $activityEnrollments = $activity->enrollments()->saveMany(
            Enrollment::factory()->count(10)->make([
                'state' => States\Confirmed::class,
            ]),
        );
        $activity->enrollments()->saveMany(
            Enrollment::factory()->count(10)->make([
                'state' => States\Created::class,
            ]),
        );
        $activity->enrollments()->saveMany(
            Enrollment::factory()->count(10)->make([
                'state' => States\Cancelled::class,
            ]),
        );
        $otherActivity->enrollments()->saveMany(
            Enrollment::factory()->count(10)->make([
                'state' => States\Confirmed::class,
            ]),
        );

        $this->actingAs(User::factory()->withRole('board')->create());

        $response = $this->getJson(route('barcode.preload', $activity))
            ->assertOk()
            ->assertJsonStructure([
                'ok',
                'data' => [
                    'salt',
                    'barcodes',
                ],
            ]);

        $salt = $response->json('data.salt');
        $barcodes = $response->json('data.barcodes');

        $this->assertCount($activityEnrollments->count(), $barcodes);

        foreach ($activityEnrollments as $enrollment) {
            $ticketHash = BarcodeController::barcodeToSecretHash($salt, $enrollment);
            $this->assertContains($ticketHash, $barcodes);
        }
    }

    /**
     * Test a simple valid consumption.
     */
    public function test_valid_consume(): void
    {
        $this->actingAs($user = User::factory()->withRole(['board'])->create());

        $activity = Activity::factory()->withTickets()->create();
        $ticket = $activity->tickets()->first();

        $enrollment = Enroll::createEnrollment($activity, $ticket);
        $enrollment->state->transitionTo(States\Paid::class);
        $enrollment->save();

        $this->actingAs($user);

        $this->postJson(route('barcode.consume', $activity), [
            'barcode' => $enrollment->ticket_code,
        ])->assertOk();

        $enrollment->refresh();

        $this->assertNotNull($enrollment->consumed_at);
        $this->assertEquals($user->id, $enrollment->consumed_by_id);

        $this->postJson(route('barcode.consume', $activity), [
            'barcode' => $enrollment->ticket_code,
        ])->assertStatus(Response::HTTP_CONFLICT);
    }

    /**
     * @dataProvider invalidStatesProvider
     */
    public function test_consuming_invalid_enrollments(string $state): void
    {
        $this->actingAs($user = User::factory()->withRole(['board'])->create());

        $activity = Activity::factory()->withTickets()->create();
        $ticket = $activity->tickets()->first();

        $enrollment = Enroll::createEnrollment($activity, $ticket);
        $enrollment->state = new $state($enrollment);
        $enrollment->save();

        $this->actingAs($user);

        $this->postJson(route('barcode.consume', $activity), [
            'barcode' => $enrollment->ticket_code,
        ])->assertNotFound();
    }

    public function invalidStatesProvider(): array
    {
        return [
            'Created' => [States\Created::class],
            'Seeded' => [States\Seeded::class],
            'Cancelled' => [States\Cancelled::class],
        ];
    }
}
