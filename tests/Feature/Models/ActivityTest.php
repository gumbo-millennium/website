<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Activity;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class ActivityTest extends TestCase
{
    use WithFaker;

    /**
     * @dataProvider provideTicketsForPriceRange
     */
    public function test_ticket_price_range(string $expected, array ...$tickets): void
    {
        Lang::setLocale('en');
        Config::set('gumbo.transfer-fee', 0);

        /** @var Activity $activity */
        $activity = factory(Activity::class)->create();

        foreach ($tickets as &$ticket) {
            $ticket['title'] ??= $this->faker->sentence();
        }

        $activity->tickets()->createMany($tickets);

        $this->assertSame($expected, $activity->price_range);
    }

    public function provideTicketsForPriceRange(): array
    {
        return [
            'no tickets' => ['Price unknown'],

            'one free ticket' => [
                'Free',
                ['price' => null],
            ],
            'two free tickets' => [
                'Free',
                ['price' => null],
                ['price' => null],
            ],

            'one paid ticket' => [
                '€ 10,-',
                ['price' => 10_00],
            ],
            'two paid tickets' => [
                'From € 10,-',
                ['price' => 10_00],
                ['price' => 20_00],
            ],
            'four paid tickets' => [
                'From € 5,-',
                ['price' => 10_00],
                ['price' => 20_00],
                ['price' => 50_00],
                ['price' => 5_00],
            ],

            'one paid, one free ticket' => [
                'Free, or paid starting at € 10,-',
                ['price' => 10_00],
                ['price' => null],
            ],
        ];
    }
}
