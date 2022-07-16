<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use Advoor\NovaEditorJs\NovaEditorJsData;
use App\Models\Page;
use Illuminate\Support\HtmlString;
use Tests\TestCase;

class PageModelTest extends TestCase
{
    /**
     * Check JSON parsing from pages.
     */
    public function test_page_contents_are_cast_and_can_be_read_as_html(): void
    {
        $page = Page::factory()->create();

        $this->assertNotNull($page->contents);
        $this->assertNotNull($page->html);

        $this->assertInstanceOf(NovaEditorJsData::class, $page->contents);
        $this->assertInstanceOf(HtmlString::class, $page->html);
    }
}
