<?php

declare(strict_types=1);

namespace Tests\Unit;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;
use Tests\Traits\CreatesDummyActivityModels;
use Tests\Traits\MutatesActivities;

/**
 * Tests a trait used for testing, sinc we should be sure all our traits work
 * as expected
 */
class MutatesActivitiesTest extends TestCase
{
    use RefreshDatabase;
    use CreatesDummyActivityModels;
    use MutatesActivities;

    /**
     * Tests selling out an event with a limit on seats
     * @return void
     */
    public function testSellOutSeatedActivity(): void
    {
        // Ensures activity exits
        $activity = $this->createDummyActivity();
        $activity->seats = 10;
        $activity->save();

        // Assert
        $this->assertSame(10, $activity->seats);
        $this->assertSame(10, $activity->available_seats);
        $this->assertEmpty($activity->enrollments);

        // Mutate
        $this->sellOutActivity($activity);

        // Check
        $this->assertNotEmpty($activity->enrollments);
        $this->assertCount(10, $activity->enrollments);
        $this->assertSame(0, $activity->available_seats);
    }

    /**
     * Tests selling out an event that did not have an assigned
     * seat count.
     * @return void
     */
    public function testSellOutNonSeatedActivity(): void
    {
        // Ensures activity exits
        $activity = $this->createDummyActivity();
        $activity->seats = null;
        $activity->save();

        // Assert
        $this->assertSame(null, $activity->seats);
        $this->assertEmpty($activity->enrollments);
        $this->assertEqualsWithDelta(\PHP_INT_MAX, $activity->available_seats, 1000);

        // Mutate
        $this->sellOutActivity($activity);

        // Check
        $this->assertSame(5, $activity->seats);
        $this->assertNotEmpty($activity->enrollments);
        $this->assertCount(5, $activity->enrollments);
        $this->assertSame(0, $activity->available_seats);
    }

    /**
     * Tests cancelling activities works and runs onconditionally
     * @return void
     */
    public function testCancellingActivity(): void
    {
        // Ensures activity exits
        $activity = $this->createDummyActivity();
        $activity->cancelled_at = null;
        $activity->save();

        // Assert
        $this->assertNull($activity->cancelled_at);

        // Cancel the activity again
        Date::setTestNow(Carbon::parse('2020-01-01T15:00:00+02:00'));
        $this->cancelActivity($activity);

        // Assert
        $this->assertInstanceOf(DateTimeInterface::class, $activity->cancelled_at);
        $this->assertSame('2020-01-01T15:00:00+02:00', $activity->cancelled_at->toIso8601String());
        $firstDate = $activity->cancelled_at;

        // Cancel the activity again
        Date::setTestNow(Carbon::parse('2020-01-01T18:00:00+02:00'));
        $this->cancelActivity($activity);

        // Assert the cancelActivity does not give a damn about existing cancellations
        $this->assertSame('2020-01-01T18:00:00+02:00', $activity->cancelled_at->toIso8601String());
        $this->assertNotEquals($firstDate, $activity->cancelled_at);
    }
}
