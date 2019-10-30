<?php

use App\Models\File;
use App\Models\FileCategory;
use Illuminate\Database\Seeder;

class FileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create a bunch of categories
        /** @var FileCategory[] $files */
        $categories = factory(FileCategory::class, random_int(3, 10))->create();

        // Create a bunch of files
        /** @var File[] $files */
        $files = factory(File::class, random_int(5, 30))->make();

        // Attach files to categories
        foreach ($files as $file) {
            $file->category()->associate($categories->random());
            $file->save();
        }
    }
}
