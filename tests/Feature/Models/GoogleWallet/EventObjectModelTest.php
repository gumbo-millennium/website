<?php

declare(strict_types=1);

namespace Tests\Feature\Models\GoogleWallet;

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\GoogleWallet\EventClass;
use App\Models\GoogleWallet\EventObject;
use Tests\TestCase;

class EventObjectModelTest extends TestCase
{
    /**
     * Check if IDs are properly assigned.
     */
    public function test_proper_id_assignment(): void
    {
        $activity = Activity::factory()->withTickets()->create();
        $enrollment = Enrollment::factory()->for($activity)->for($activity->tickets->first())->create();

        $class = EventClass::factory()->for($activity, 'subject')->make();
        $object = EventObject::factory()->for($enrollment, 'subject')->for($class, 'class')->make();

        $this->assertNull($class->wallet_id);

        $class->save();

        $this->assertNotNull($class->wallet_id);
        $this->assertStringContainsString(sprintf('AC%04d', $activity->id), $class->wallet_id);
    }
}
