<?php

declare(strict_types=1);

use App\Helpers\Str;
use App\Models\Activity;
use App\Models\Ticket;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seedTestingEvents();
    }

    /**
     * Seeds a bunch of events that start soon.
     */
    private function seedTestingEvents(): void
    {
        $faker = App::make(\Faker\Generator::class);

        $sets = [
            'private-free' => [[true, null]],
            'private-paid' => [[true, 15_00]],
            'public-free' => [[false, null]],
            'public-member' => [[false, 15_00], [true, null]],
            'public-paid' => [[false, 15_00]],
            'public-short-discount' => [[false, 15_00], [true, 5_00, 5]],
            'public-discount' => [[false, 15_00], [true, 5_00]],
        ];

        // Date
        $startDate = Carbon::today()->addWeek()->setHour(20)->toImmutable();
        $endDate = $startDate->addHours(3);

        // Iterate
        foreach ($sets as $slug => $tickets) {
            // Increase the dates
            $startDate = $startDate->addDay();
            $endDate = $endDate->addDay();

            $tickets = Collection::make($tickets);
            $name = Str::title(str_replace('-', ' ', $slug));

            if (Activity::query()->whereSlug($slug)->exists()) {
                continue;
            }

            $activity = factory(Activity::class)->states(['with-image'])->create([
                'name' => "[test] {$name}",
                'slug' => $slug,
                'tagline' => optional($faker)->sentence,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_public' => $tickets->min('0') === false,
                'seats' => 15,
            ]);

            foreach ($tickets as $index => $ticket) {
                $memberOnly = $ticket[0];
                $price = $ticket[1];
                $quantity = $ticket[2] ?? null;

                $activity->tickets()->firstOrCreate([
                    'title' => __('Ticket :number', ['number' => $index + 1]),
                ], [
                    'price' => $price,
                    'members_only' => (bool) $memberOnly,
                    'quantity' => $quantity,
                ]);
            }
        }

        // Seed an activity with a form
        if (! Activity::query()->whereSlug('with-form')->exists()) {
            $activity = factory(Activity::class)->states(['with-image', 'with-form'])->create([
                'name' => '[test] With Form',
                'slug' => 'with-form',
                'tagline' => optional($faker)->sentence,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            $activity->tickets()->saveMany([
                factory(Ticket::class)->make([
                    'price' => 25_00,
                ]),
                factory(Ticket::class)->states(['private'])->make([
                    'quantity' => 5,
                    'price' => 10_00,
                ]),
                factory(Ticket::class)->states(['private'])->make([
                    'price' => 15_00,
                ]),
            ]);
        }
    }
}
