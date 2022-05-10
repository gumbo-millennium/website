<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Activity;
use Illuminate\Support\Facades\Date;
use Tests\Feature\Http\Controllers\Shop\Traits\TestsShop;
use Tests\TestCase;

class HomepageControllerTest extends TestCase
{
    use TestsShop;

    public function test_empty_homepage(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_full_homepage(): void
    {
        Activity::query()->forceDelete();

        [$public1, $private, $public2] = Activity::factory()->createMany([[
            'name' => 'Public event 1',
            'is_public' => true,
            'start_date' => $start = Date::now()->addWeeks(1),
            'end_date' => (clone $start)->addHours(3),
            'published_at' => null,
        ], [
            'name' => 'Private event 1',
            'is_public' => false,
            'start_date' => $start = Date::now()->addWeeks(2),
            'end_date' => (clone $start)->addHours(3),
            'published_at' => null,
        ], [
            'name' => 'Public event 2',
            'is_public' => true,
            'start_date' => $start = Date::now()->addWeeks(3),
            'end_date' => (clone $start)->addHours(3),
            'published_at' => null,
        ]]);

        $advertisedProduct = $this->getProductVariant()->product;
        $advertisedProduct->advertise_on_home = true;
        $advertisedProduct->save();

        $this->get('/')
            ->assertOk()
            ->assertSee($advertisedProduct->name)
            ->assertSee($public1->name)
            ->assertDontSee($private->name)
            ->assertSee($public2->name);

        $this->actingAs($this->getGuestUser());
        $this
            ->get('/')
            ->assertOk()
            ->assertSee($advertisedProduct->name)
            ->assertSee($public1->name)
            ->assertDontSee($private->name)
            ->assertSee($public2->name);

        $this->actingAs($this->getMemberUser());
        $this
            ->get('/')
            ->assertOk()
            ->assertSee($advertisedProduct->name)
            ->assertSee($public1->name)
            ->assertSee($private->name)
            ->assertSee($public2->name);
    }

    public function test_display_flash_messages(): void
    {
        flash()->warning('Something went wrong, this is the message');

        $this->get('/')
            ->assertOk()
            ->assertSee('Something went wrong, this is the message');
    }
}
