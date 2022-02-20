<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Page;
use Tests\TestCase;

class PageModelTest extends TestCase
{
    /**
     * Check JSON parsing from pages.
     */
    public function test_page_contents_are_cast_from_json(): void
    {
        $page = Page::factory()->create();

        $this->assertIsString($page->contents);
        $this->assertArrayHasKey('contents', $page->getCasts());
        $this->assertSame('json', $page->getCasts()['contents']);
    }
}
