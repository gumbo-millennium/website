<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\FileCategory;

/**
 * Generates a default category for the files to be placed in.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
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
        // Ensure default category
        if (FileCategory::findDefault() === null) {
            FileCategory::updateOrCreate([
                'slug' => 'overig'
            ], [
                'title' => 'Overige',
                'default' => true
            ]);
        }
    }
}
