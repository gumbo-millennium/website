<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Helpers\Str;
use App\Models\Page;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

class PageControllerTest extends TestCase
{
    private const GIT_FILE = 'privacy-policy';

    public function test_git_page(): void
    {
        $this->artisan('gumbo:update-content');

        $file = self::GIT_FILE;

        $finder = Finder::create()
            ->in(resource_path('assets/json/pages'))
            ->name('*.json')
            ->files();

        $pageTable = Page::make()->getTable();

        foreach ($finder as $file) {
            $this->assertFileExists($file->getPathname());

            $slug = Str::beforeLast($file->getFilename(), '.');

            $this->assertDatabaseHas($pageTable, [
                'slug' => $slug,
            ]);

            $this->get("/${slug}")->assertOk();
        }
    }
}
