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
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BarcodeControllerTest extends TestCase
{
    public function test_unauthorized_access_request(): void
    {
        $user = User::factory()->withRole(['member'])->create();

        $activity = Activity::factory()->withTickets()->create();
        $ticket = $activity->tickets()->first();

        $enrollment = $activity->enrollments()->save(
            Enrollment::factory()
                ->for($user)
                ->for($ticket)
                ->make([
                    'state' => States\Confirmed::class,
                ]),
        );

        $preloadRoute = route('barcode.preload', $activity);
        $consumeRoute = route('barcode.consume', $activity);

        $this->getJson($preloadRoute)->assertUnauthorized();
        $this->postJson($consumeRoute, ['barcode' => $enrollment->ticket_code])
            ->assertUnauthorized();

        Sanctum::actingAs($user, ['*']);

        $this->getJson($preloadRoute)->assertForbidden();
        $this->postJson($consumeRoute, ['barcode' => $enrollment->ticket_code])
            ->assertForbidden();
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

        foreach ($activityEnrollments as $enrollment) {
            $ticketHash = BarcodeController::barcodeToSecretHash($salt, $enrollment->ticket_code);
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
        $enrollment->transitionTo(States\Paid::class);
        $enrollment->save();

        Sanctum::actingAs($user, ['*']);

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

        Sanctum::actingAs($user, ['*']);

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
