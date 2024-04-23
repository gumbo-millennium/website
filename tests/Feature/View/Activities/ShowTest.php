<?php

declare(strict_types=1);

namespace Tests\Feature\View\Activities;

use App\Helpers\Str;
use App\Models\Activity;
use Tests\TestCase;

class ShowTest extends TestCase
{
    /**
     * A basic view test example.
     */
    public function test_it_can_render(): void
    {
        /** @var Activity $activity */
        $activity = Activity::factory()->withTickets()->create();
        assert($activity->tickets->count() == 2);

        [$freeTicket, $paidTicket] = $activity->tickets->sortBy('price');

        $freeTicket->update(['price' => null]);
        $paidTicket->update(['price' => 25_00]);

        $contents = $this->view('activities.show', [
            'activity' => $activity,
        ]);

        $contents->assertSee($activity->name);
        $contents->assertSee(sprintf('%s of %s', __('Free'), Str::price($paidTicket->total_price)));
    }
}
