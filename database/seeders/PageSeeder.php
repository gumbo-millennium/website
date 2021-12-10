<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (Page::getRequiredPages() as $slug => $title) {
            Page::query()->updateOrCreate(
                ['slug' => $slug, 'group' => null],
                ['title' => $title, 'type' => Page::TYPE_REQUIRED],
            );
        }
    }
}
