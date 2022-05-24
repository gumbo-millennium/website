<?php

declare(strict_types=1);

namespace Tests\Feature\View\Components\cards;

use App\Models\Activity;
use App\Models\Ticket;
use App\View\Components\Cards\Activity as ActivityCard;
use Tests\TestCase;
use Tests\Traits\TestsComponents;

class ActivityTest extends TestCase
{
    use TestsComponents;

    /**
     * Tests a public event without tickets (the most basic of events).
     */
    public function test_public_ticketless_activity(): void
    {
        $activity = Activity::factory()
            ->public()
            ->create();

        $this->renderComponent(ActivityCard::class, [
            'activity' => $activity,
        ])
            ->assertOk()
            ->assertSee(__('Public activity'));
    }

    /**
     * Tests a public event with free tickets.
     */
    public function test_public_free_ticket_activity(): void
    {
        $activity = Activity::factory()
            ->public()
            ->create();

        $activity->tickets()->saveMany([
            Ticket::factory()->make(),
        ]);

        $this->renderComponent(ActivityCard::class, [
            'activity' => $activity,
        ])
            ->assertOk()
            ->assertSee(__('Public activity'));
    }

    /**
     * Tests a public event with free tickets for members, and paid for public.
     */
    public function test_public_mixed_ticket_activity(): void
    {
        $activity = Activity::factory()
            ->public()
            ->create();

        $activity->tickets()->saveMany([
            Ticket::factory()->paid()->make(),
            Ticket::factory()->private()->make(),
        ]);

        $this->renderComponent(ActivityCard::class, [
            'activity' => $activity,
        ])
            ->assertOk()
            ->assertSee(__('Paid activity'));
    }

    /**
     * Tests a private event without tickets.
     */
    public function test_private_ticketless_activity(): void
    {
        $activity = Activity::factory()
            ->private()
            ->create();

        $this->renderComponent(ActivityCard::class, [
            'activity' => $activity,
        ])
            ->assertOk()
            ->assertSee(__('Private activity'));
    }

    /**
     * Tests a private event with free tickets.
     */
    public function test_private_free_tickets(): void
    {
        $activity = Activity::factory()
            ->private()
            ->create();

        $activity->tickets()->saveMany([
            Ticket::factory()->make(),
        ]);

        $this->renderComponent(ActivityCard::class, [
            'activity' => $activity,
        ])
            ->assertOk()
            ->assertSee(__('Private activity'));
    }

    /**
     * Tests a private event with paid tickets.
     */
    public function test_private_paid_tickets(): void
    {
        $activity = Activity::factory()
            ->private()
            ->create();

        $activity->tickets()->saveMany([
            Ticket::factory()->paid()->make(),
        ]);

        $this->renderComponent(ActivityCard::class, [
            'activity' => $activity,
        ])
            ->assertOk()
            ->assertSee(__('Paid activity'));
    }
}
