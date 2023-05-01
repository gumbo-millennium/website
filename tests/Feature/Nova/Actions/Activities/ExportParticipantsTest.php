<?php

declare(strict_types=1);

namespace Tests\Feature\Nova\Actions\Activities;

use App\Models\Activity;
use App\Models\States\Enrollment\Confirmed;
use App\Models\States\Enrollment\Created;
use App\Models\States\Enrollment\Paid;
use App\Models\States\Enrollment\Seeded;
use App\Models\User;
use App\Nova\Actions\Activities\ExportParticipants;
use Illuminate\Support\Facades\Auth;
use Tests\Feature\Nova\NovaTestCase;

class ExportParticipantsTest extends NovaTestCase
{
    /**
     * Test activity without any participants.
     */
    public function test_empty_activity(): void
    {
        $activity = Activity::factory()->create();

        $this->callActionOnModel(new ExportParticipants(), $activity)
            ->assertOk()
            ->assertDownload();
    }

    /**
     * Test activity check in list creation with a bunch of participants.
     */
    public function test_activity_check_in(): void
    {
        $activity = $this->getActivityWithParticipants([
            Paid::class => 8,
            Confirmed::class => 2,
            Created::class => 2,
            Seeded::class => 4,
        ]);

        $this->callActionOnModel(new ExportParticipants(), $activity, [
            'type' => ExportParticipants::TYPE_CHECK_IN,
        ])
            ->assertOk()
            ->assertDownload();
    }

    /**
     * Test activity check in list creation with a bunch of participants.
     */
    public function test_activity_medical(): void
    {
        $activity = $this->getActivityWithParticipants([
            Paid::class => 8,
            Confirmed::class => 2,
            Created::class => 2,
            Seeded::class => 4,
        ]);

        $this->callActionOnModel(new ExportParticipants(), $activity, [
            'type' => ExportParticipants::TYPE_ARCHIVE,
        ])
            ->assertOk()
            ->assertDownload();
    }

    private function getActivityWithParticipants(array $states = []): Activity
    {
        /** @var Activity $activity */
        $activity = Activity::factory()->create();

        /** @var \App\Models\Ticket $freeTicket */
        /** @var \App\Models\Ticket $paidTicket */
        $tickets = $activity->tickets()->createMany([
            [
                'title' => 'Free',
            ],
            [
                'title' => 'Paid',
                'price' => 10_00,
            ],
        ]);

        $users = User::factory()->times(array_sum($states))->create();

        foreach ($states as $state => $count) {
            for ($iteration = 0; $iteration < $count; $iteration++) {
                $ticket = $tickets->random();

                $activity->enrollments()->create([
                    'user_id' => $users->pop()?->id ?? 0,
                    'ticket_id' => $ticket->id,
                    'state' => $state::$name,
                    'price' => $ticket->price,
                    'total_price' => $ticket->total_price,
                ]);
            }
        }

        Auth::logout();

        return $activity;
    }
}
