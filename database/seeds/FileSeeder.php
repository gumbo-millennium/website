<?php

declare(strict_types=1);

use App\Models\File;
use App\Models\FileCategory;
use Illuminate\Database\Seeder;

class FileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        // Create a bunch of categories
        /** @var array<FileCategory> $files */
        $categories = factory(FileCategory::class, random_int(3, 10))->create();

        // Create a bunch of files for each category
        foreach ($categories as $category) {
            // Create a bunch of files in this category
            factory(File::class, random_int(5, 30))->create([
                'category_id' => $category->id
            ]);
        }
    }
}
