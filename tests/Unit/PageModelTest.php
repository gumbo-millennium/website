<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Page;
use PHPUnit\Framework\TestCase;

class PageModelTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testPageContentsAreStrings(): void
    {
        $page = factory(Page::class)->create();

        $this->assertIsString($page->contents);
        $this->assertArrayNotHasKey('contents', $page->getCasts());
    }
}
