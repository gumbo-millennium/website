<?php

declare(strict_types=1);

namespace Tests\Feature\Models\GoogleWallet;

use App\Models\Activity;
use App\Models\GoogleWallet\EventClass;
use Tests\TestCase;

class EventClassModelTest extends TestCase
{
    /**
     * Check if IDs are properly assigned.
     */
    public function test_proper_id_assignment(): void
    {
        $activity = Activity::factory()->create();

        $class = EventClass::factory()->for($activity, 'subject')->make();

        $this->assertNull($class->wallet_id);

        $class->save();

        $this->assertNotNull($class->wallet_id);
        $this->assertStringContainsString(sprintf('AC%06d', $activity->id), $class->wallet_id);
    }
}
