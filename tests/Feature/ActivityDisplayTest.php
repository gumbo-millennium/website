<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Activity;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class ActivityDisplayTest extends TestCase
{
    /**
     * Get test route.
     */
    public function test_various_routes(): void
    {
        $publicEvent = factory(Activity::class)->create([
            'is_public' => true,
            'published_at' => Date::today()->subYear(),
            'start_date' => Date::today()->addWeeks(1),
            'end_date' => Date::today()->addWeeks(1)->addHours(5),
        ]);

        $privateEvent = factory(Activity::class)->create([
            'is_public' => false,
            'published_at' => Date::today()->subYear(),
            'start_date' => Date::today()->addWeeks(2),
            'end_date' => Date::today()->addWeeks(2)->addHours(5),
        ]);

        $this->get(route('activity.show', $publicEvent))
            ->assertOk();

        $this->get(route('activity.show', $privateEvent))
            ->assertRedirect(route('login'));

        $this->get(route('activity.index'))
            ->assertSeeText($publicEvent->name)
            ->assertDontSeeText($privateEvent->name);

        $this->actingAs($this->getGuestUser());

        $this->get(route('activity.show', $publicEvent))
            ->assertOk();

        $this->get(route('activity.show', $privateEvent))
            ->assertForbidden();

        $this->get(route('activity.index'))
            ->assertSeeText($publicEvent->name)
            ->assertDontSeeText($privateEvent->name);

        $this->actingAs($this->getMemberUser());

        $this->get(route('activity.show', $publicEvent))
            ->assertOk();

        $this->get(route('activity.show', $privateEvent))
            ->assertOk();

        $this->get(route('activity.index'))
            ->assertSeeText($publicEvent->name)
            ->assertSeeText($privateEvent->name);
    }
}
