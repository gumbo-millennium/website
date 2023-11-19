<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Account;

use App\Facades\Enroll;
use App\Models\Activity;
use Tests\TestCase;

class TicketControllerTest extends TestCase
{
    public function test_guest_condition(): void
    {
        $this->get(route('account.tickets'))
            ->assertRedirect(route('login'));
    }

    public function test_empty_state(): void
    {
        $this->actingAs($this->getGuestUser());

        $this->get(route('account.tickets'))
            ->assertSuccessful()
            ->assertSee('data-content="ticket-state-empty"', false);
    }

    public function test_normal_state(): void
    {
        $this->actingAs($this->getGuestUser());

        $activity = Activity::factory()->withTickets()->create();
        Enroll::createEnrollment($activity, $ticket = $activity->tickets->first());

        $this->get(route('account.tickets'))
            ->assertSuccessful()
            ->assertSee($activity->name)
            ->assertSee($ticket->name)
            ->assertSee(route('enroll.show', $activity));
    }
}
