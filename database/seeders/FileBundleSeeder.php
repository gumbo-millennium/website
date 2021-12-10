<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FileBundle;
use App\Models\FileCategory;
use Illuminate\Database\Seeder;

class FileBundleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws Exception
     */
    public function run()
    {
        // Create a bunch of categories
        /** @var array<FileCategory> $files */
        $categories = FileCategory::factory()->times(random_int(1, 5))->create();

        // Create a bunch of files for each category
        foreach ($categories as $category) {
            // Create a bunch of files in this category
            FileBundle::factory()->times(random_int(2, 12))->create([
                'category_id' => $category->id,
            ]);
        }
    }
}
