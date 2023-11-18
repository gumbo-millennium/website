<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Ensure all required pages and page groups exist.
         */
        foreach (Page::getRequiredPages() as $slug => $title) {
            Page::query()->updateOrCreate(
                ['slug' => $slug, 'group' => null],
                ['title' => $title, 'type' => Page::TYPE_REQUIRED],
            );
        }

        /**
         * Ensure all mini-sites have a home page.
         */
        foreach (Config::get('gumbo.minisites') as $group => $config) {
            if (! Arr::get($config, 'enabled')) {
                continue;
            }

            $page = Page::firstOrNew(['slug' => 'home', 'group' => $group]);
            $page->type = Page::TYPE_REQUIRED;
            $page->title ??= $group;
            $page->save();
        }
    }
}
