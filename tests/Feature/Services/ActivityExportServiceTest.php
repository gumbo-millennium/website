<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Enums\ActivityExportType;
use App\Models\Activity;
use App\Models\States\Enrollment\Confirmed;
use App\Models\States\Enrollment\Created;
use App\Models\User;
use App\Services\ActivityExportService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ActivityExportServiceTest extends TestCase
{
    /**
     * Test exports in general.
     *
     * @dataProvider provideTypes
     */
    public function test_creation(ActivityExportType $exportType): void
    {
        $activity = Activity::factory()->withTickets()->create();

        foreach (User::factory()->times(6)->create() as $pendingUser) {
            $activity->enrollments()->create([
                'user_id' => $pendingUser->id,
                'ticket_id' => $activity->tickets->random()->id,
                'state' => Created::NAME,
            ]);
        }

        foreach (User::factory()->times(11)->create() as $confirmedUser) {
            $activity->enrollments()->create([
                'user_id' => $confirmedUser->id,
                'ticket_id' => $activity->tickets->random()->id,
                'state' => Confirmed::NAME,
            ]);
        }

        Storage::fake(Storage::getDefaultCloudDriver());

        $service = App::make(ActivityExportService::class);
        $this->assertInstanceOf(ActivityExportService::class, $service);

        $result = $service->createParticipantsExport($activity, $exportType);

        $this->assertNotEmpty($result);
        Storage::cloud()->assertExists($result);
    }

    public function provideTypes(): array
    {
        return [
            'Check-in' => [ActivityExportType::CheckIn],
            // 'Medical' => [ActivityExportType::Medical],
            'Full' => [ActivityExportType::Full],
        ];
    }
}
