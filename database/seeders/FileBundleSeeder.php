<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FileBundle;
use App\Models\FileCategory;
use Faker\Generator;
use Illuminate\Database\Seeder;

class FileBundleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Generator $faker)
    {
        // Create a bunch of categories
        /** @var array<FileCategory> $files */
        $categories = FileCategory::factory($faker->numberBetween(1, 5))->create();

        // Create a bunch of files for each category
        foreach ($categories as $category) {
            // Create a bunch of files in this category
            FileBundle::factory($faker->numberBetween(2, 12))->create([
                'category_id' => $category->id,
            ]);
        }
    }
}
