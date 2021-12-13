<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Page;
use PHPUnit\Framework\TestCase;

class PageModelTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_page_contents_are_strings(): void
    {
        $page = Page::factory()->create();

        $this->assertIsString($page->contents);
        $this->assertArrayNotHasKey('contents', $page->getCasts());
    }
}
