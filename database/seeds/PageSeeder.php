<?php

declare(strict_types=1);

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        foreach (Page::REQUIRED_PAGES as $slug => $title) {
            Page::updateOrCreate(
                ['slug' => $slug],
                ['title' => $title, 'type' => Page::TYPE_REQUIRED]
            );
        }
    }
}
