<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Google\WalletService;

use App\Facades\Enroll;
use App\Models\Activity;
use App\Models\GoogleWallet\EventClass;
use App\Models\GoogleWallet\EventObject;
use App\Models\States\Enrollment\Confirmed;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Google\Traits\HandlesModels;
use Tests\TestCase;

class HandlesModelsTest extends TestCase
{
    use HandlesModels;

    /**
     * Test if regular conversion works as expected from activities.
     */
    public function test_activity_to_eventclass_conversion(): void
    {
        $activity = Activity::factory()->withTickets()->create();

        $eventClass = $this->buildEventClassForActivity($activity);

        $this->assertInstanceOf(EventClass::class, $eventClass);
        $this->assertTrue($eventClass->exists);
        $this->assertTrue($activity->is($eventClass->subject), 'Failed asserting that the event class is for the activity.');

        $this->assertSame($activity->name, $eventClass->name);
        $this->assertSame($activity->start_date->toIso8601String(), $eventClass->start_time->toIso8601String());
        $this->assertSame($activity->end_date->toIso8601String(), $eventClass->end_time->toIso8601String());
    }

    /**
     * Test if regular conversion works as expected for enrollments.
     * @depends test_activity_to_eventclass_conversion
     */
    public function test_enrollment_to_eventobject_conversion(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()->create();
        $ticket = Ticket::factory()->for($activity)->create([
            'price' => 60_00,
        ]);

        $this->actingAs($user);
        $enrollment = Enroll::createEnrollment($activity, $ticket);
        $enrollment->transitionTo(Confirmed::class);

        $eventClass = $this->buildEventClassForActivity($activity);
        $this->assertTrue($eventClass->exists, 'Failed to create the eventClass');

        $eventObject = $this->buildEventObjectForEnrollment($enrollment);

        $this->assertInstanceOf(EventObject::class, $eventObject);
        $this->assertTrue($eventObject->exists);
        $this->assertTrue($enrollment->is($eventObject->subject), 'Failed asserting that the event object is for the enrollment.');
        $this->assertTrue($eventClass->is($eventObject->class), 'Failed asserting that the event object is for the event class.');
        $this->assertTrue($user->is($eventObject->owner), 'Failed asserting that the event object is owned by the user.');

        $this->assertTrue(money_value($enrollment->total_price)?->isEqualTo($eventObject->value));
    }
}
