<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\NewsItem;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Tests the following flow
 *
 * - Loading the news page
 * - Creating a new news item and checking if it's shown
 * - Reading our generated news item
 * - Deleting our news item and checking if the page sends a 404
 * - Ensuring the index doesn't have our item anymore
 */
class NewsItemTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Returns a freshly created news item
     * @return NewsItem
     * @throws BindingResolutionException
     */
    protected function getNewsItem(): NewsItem
    {
        // Create random item with a unique title
        return \factory(NewsItem::class, 1)->create()->first();
    }

    /**
     * A basic feature test example.
     * @return void
     */
    public function testIndex(): void
    {
        // Get news index
        $response = $this->get(route('news.index'));

        // Ensure it loads
        $response->assertOk();
    }

    /**
     * Tests if the newly created news item is shown
     * @return void
     */
    public function testIndexWithItem(): void
    {
        // Get item
        $item = $this->getNewsItem();

        // Get news index
        $response = $this->get(route('news.index'));

        // Ensure it loads
        $response->assertOk();

        // Check if we have our article
        $response->assertSeeText($item->title);
    }

    /**
     * Tests if the item can be seen
     * @return void
     */
    public function testViewItem(): void
    {
        // Get item
        $item = $this->getNewsItem();

        // Get news index
        $response = $this->get(route('news.show', ['news' => $item]));

        // Ensure it loads
        $response->assertOk();

        // Check if we see our article's title
        $response->assertSeeText($item->title);
    }

    /**
     * Tests if an item that's deleted, returns a 404
     * @return void
     */
    public function testViewDeletedItem(): void
    {
        // Get item
        $item = $this->getNewsItem();

        // Delete item
        $item->delete();

        // Get news index
        $response = $this->get(route('news.show', ['news' => $item]));

        // Ensure it loads
        $response->assertNotFound();
    }

    /**
     * Tests if an item that's deleted, isn't shown on the cover (of Vogue)
     * @return void
     */
    public function testIndexWithoutDeletedItem(): void
    {
        // Get item
        $item = $this->getNewsItem();
        $item2 = $this->getNewsItem();

        // Delete item
        $item->delete();

        // Get news index
        $response = $this->get(route('news.index'));

        // Ensure it loads
        $response->assertOk();

        // Check if we cannot see our article
        $response->assertDontSeeText($item->title);
        $response->assertSeeText($item2->title);
    }
}
