<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Page;
use Tests\TestCase;

class PageControllerTest extends TestCase
{
    private const GIT_FILE = 'privacy-policy';

    public function testHomepage(): void
    {
        $this->get('/')
            ->assertOk();
    }

    public function testGitPage(): void
    {
        $file = self::GIT_FILE;

        $this->assertFileExists(resource_path("assets/json/pages/{$file}.json"));

        $this->assertTrue(Page::whereSlug($file)->exists());

        $this->get("/{$file}")
            ->assertOk();
    }
}
