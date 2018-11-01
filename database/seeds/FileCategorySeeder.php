<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\FileCategory;

/**
 * Generates a default category for the files to be placed in
 */
class FileCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        FileCategory::create([
            'slug' => 'overig',
            'title' => 'Overige',
            'default' => true
        ]);
    }
}
