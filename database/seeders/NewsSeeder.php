<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\NewsItem;
use Faker\Generator;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Generator $faker)
    {
        NewsItem::factory($faker->numberBetween(5, 15))->create();
    }
}
